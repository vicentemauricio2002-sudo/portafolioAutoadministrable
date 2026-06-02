document.getElementById('loginModalForm').addEventListener('submit', function(e) {
    var usuario = document.getElementById('modalUsuario').value.trim();
    var contrasena = document.getElementById('modalPassword').value;

    // Aquí defines las credenciales reales que vas a usar (Cámbialas si quieres otras)
    var usuarioCorrecto = "vicente";
    var contrasenaCorrecta = "123456";

    // Validamos si lo que escribió es diferente a lo correcto
    if (usuario !== usuarioCorrecto || contrasena !== contrasenaCorrecta) {
        // Detenemos el envío del formulario
        e.preventDefault();
        alert("Usuario o contraseña incorrectos. Por favor, inténtelo de nuevo.");
    }
    // Si los datos son correctos, no entra al IF y el formulario continúa
    // viajando de forma normal hacia "dashboard_admin.php" definido en tu action.
});