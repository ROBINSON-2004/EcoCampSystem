/**
 * JavaScript para gestión de formularios
 * EcoCamSystem
 */

// Objeto principal de formularios
const FormulariosManager = {
    
    /**
     * Cambiar estado de un formulario (activo/inactivo)
     */
    cambiarEstado: function(idFormulario, nuevoEstado) {
        if (!confirm('¿Cambiar el estado de este formulario?')) {
            return;
        }
        
        fetch('/api/formularios.php?accion=cambiar_estado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${idFormulario}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje('Estado actualizado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarMensaje(data.mensaje, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al cambiar estado', 'danger');
        });
    },
    
    /**
     * Asignar formulario a campistas seleccionados
     */
    asignarACampistas: function(idFormulario) {
        const checkboxes = document.querySelectorAll('input[name="campistas[]"]:checked');
        
        if (checkboxes.length === 0) {
            alert('Debe seleccionar al menos un campista');
            return;
        }
        
        const campistaIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (!confirm(`¿Asignar este formulario a ${campistaIds.length} campista(s)?`)) {
            return;
        }
        
        fetch('/api/formularios.php?accion=asignar_campistas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_formulario=${idFormulario}&campistas=${JSON.stringify(campistaIds)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(data.mensaje, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarMensaje(data.mensaje, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al asignar formulario', 'danger');
        });
    },
    
    /**
     * Asignar formulario a todos los campistas activos
     */
    asignarATodos: function(idFormulario) {
        if (!confirm('¿Asignar este formulario a TODOS los campistas activos?')) {
            return;
        }
        
        fetch('/api/formularios.php?accion=asignar_todos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_formulario=${idFormulario}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(data.mensaje, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarMensaje(data.mensaje, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al asignar formulario', 'danger');
        });
    },
    
    /**
     * Eliminar asignación de formulario a campista
     */
    eliminarAsignacion: function(idFormulario, idCampista, nombreCampista) {
        if (!confirm(`¿Eliminar la asignación de este formulario para ${nombreCampista}?`)) {
            return;
        }
        
        fetch('/api/formularios.php?accion=eliminar_asignacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_formulario=${idFormulario}&id_campista=${idCampista}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensaje(data.mensaje, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarMensaje(data.mensaje, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al eliminar asignación', 'danger');
        });
    },
    
    /**
     * Cargar formularios pendientes (para padres)
     */
    cargarFormulariosPendientes: function(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
        
        fetch('/api/formularios.php?accion=formularios_padre&firmado=0')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formularios = data.formularios;
                
                if (formularios.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            ¡No tienes formularios pendientes!
                        </div>
                    `;
                    return;
                }
                
                let html = '<div class="list-group">';
                formularios.forEach(form => {
                    const badge = form.obligatorio ? 
                        '<span class="badge badge-danger">Obligatorio</span>' : 
                        '<span class="badge badge-info">Opcional</span>';
                    
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${escapeHtml(form.titulo)} ${badge}</h6>
                                    <small class="text-muted">Para: ${escapeHtml(form.campista_nombre)}</small>
                                </div>
                                <a href="/vistas/padre/formularios/firmar.php?id_formulario=${form.id_formulario}&id_campista=${form.id_campista}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-signature"></i> Firmar
                                </a>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                container.innerHTML = html;
            } else {
                container.innerHTML = `<div class="alert alert-danger">${data.mensaje}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar formularios</div>';
        });
    },
    
    /**
     * Cargar próximos a vencer (para dashboard admin)
     */
    cargarProximosVencer: function(containerId, dias = 7) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
        
        fetch(`/api/formularios.php?accion=proximos_vencer&dias=${dias}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formularios = data.formularios;
                
                if (formularios.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            No hay formularios próximos a vencer
                        </div>
                    `;
                    return;
                }
                
                let html = '<div class="list-group">';
                formularios.forEach(form => {
                    const diasRestantes = form.dias_restantes || 0;
                    const color = diasRestantes <= 1 ? 'danger' : 'warning';
                    
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${escapeHtml(form.formulario_titulo)}</h6>
                                    <small class="text-muted">
                                        ${escapeHtml(form.campista_nombre)} ${escapeHtml(form.campista_apellidos)}
                                    </small>
                                </div>
                                <span class="badge badge-${color}">
                                    ${diasRestantes} día(s)
                                </span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                container.innerHTML = html;
            } else {
                container.innerHTML = `<div class="alert alert-danger">${data.mensaje}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar datos</div>';
        });
    },
    
    /**
     * Seleccionar/Deseleccionar todos los campistas
     */
    toggleSeleccionarTodos: function(checkboxMaster) {
        const checkboxes = document.querySelectorAll('input[name="campistas[]"]');
        checkboxes.forEach(cb => {
            cb.checked = checkboxMaster.checked;
        });
        this.actualizarContadorSeleccionados();
    },
    
    /**
     * Actualizar contador de campistas seleccionados
     */
    actualizarContadorSeleccionados: function() {
        const contador = document.getElementById('contador-seleccionados');
        if (!contador) return;
        
        const seleccionados = document.querySelectorAll('input[name="campistas[]"]:checked').length;
        contador.textContent = seleccionados;
    },
    
    /**
     * Filtrar tabla de campistas
     */
    filtrarCampistas: function(termino) {
        const filas = document.querySelectorAll('#tabla-campistas tbody tr');
        const terminoLower = termino.toLowerCase();
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(terminoLower) ? '' : 'none';
        });
    }
};

// Funciones auxiliares
function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    `;
    
    document.body.appendChild(alerta);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        alerta.remove();
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
    // Si hay checkboxes de campistas, agregar evento de actualización
    const checkboxesCampistas = document.querySelectorAll('input[name="campistas[]"]');
    if (checkboxesCampistas.length > 0) {
        checkboxesCampistas.forEach(cb => {
            cb.addEventListener('change', () => FormulariosManager.actualizarContadorSeleccionados());
        });
        FormulariosManager.actualizarContadorSeleccionados();
    }
    
    // Si hay campo de búsqueda de campistas
    const buscadorCampistas = document.getElementById('buscar-campistas');
    if (buscadorCampistas) {
        buscadorCampistas.addEventListener('input', (e) => {
            FormulariosManager.filtrarCampistas(e.target.value);
        });
    }
});