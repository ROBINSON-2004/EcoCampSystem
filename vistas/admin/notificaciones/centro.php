<div class="container">
    <div class="toolbar" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="../../panel.php" class="btn-back"><span>←</span> Inicio</a>
        <h1>📢 Centro de Notificaciones y Emergencias</h1>
    </div>

    <div class="card shadow" style="margin-top: 20px;">
        <h3>Eventos Próximos (Masivo a Padres)</h3>
        <form action="procesar.php" method="POST">
            <input type="hidden" name="accion" value="masiva">
            <input type="text" name="titulo" placeholder="Título del Evento" class="form-control" required>
            <textarea name="mensaje" placeholder="Describe el evento..." class="form-control" rows="3"></textarea>
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Enviar a Todos los Padres</button>
        </form>
    </div>

    <div class="card shadow" style="margin-top: 25px;">
        <h3>Agenda Diaria por Grupos</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Actividad</th>
                    <th>Horario</th>
                    <th>Notificar Consejero</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Exploradores A</td>
                    <td>Senderismo de Montaña</td>
                    <td>09:00 - 11:00</td>
                    <td><button class="btn-small">📲 Enviar Agenda</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>