let map;
let residenceMarker, workMarker;
let activeMarker = null; // Variable para controlar el marcador activo

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 4.5709, lng: -74.2973 }, // Coordenadas iniciales de Colombia
        zoom: 6
    });

    // Evento de clic para establecer marcador según selección (residencia o trabajo)
    map.addListener('click', (event) => {
        if (activeMarker === 'residence') {
            placeResidenceMarker(event.latLng);
        } else if (activeMarker === 'work') {
            placeWorkMarker(event.latLng);
        }
    });
}

// Funciones para activar los marcadores
function setResidenceMarker() {
    activeMarker = 'residence';
    alert('Selecciona un punto en el mapa para la residencia');
}

function setWorkMarker() {
    activeMarker = 'work';
    alert('Selecciona un punto en el mapa para el trabajo');
}

// Coloca el marcador de residencia
function placeResidenceMarker(location) {
    if (residenceMarker) {
        residenceMarker.setPosition(location);
    } else {
        residenceMarker = new google.maps.Marker({
            position: location,
            map: map,
            title: "Residencia"
        });
    }
    document.getElementById('latitud_residencia').value = location.lat();
    document.getElementById('longitud_residencia').value = location.lng();
}

// Coloca el marcador de trabajo
function placeWorkMarker(location) {
    if (workMarker) {
        workMarker.setPosition(location);
    } else {
        workMarker = new google.maps.Marker({
            position: location,
            map: map,
            title: "Trabajo"
        });
    }
    document.getElementById('latitud_trabajo').value = location.lat();
    document.getElementById('longitud_trabajo').value = location.lng();
}

document.getElementById('formEstudiante').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const cedula = document.getElementById('cedula').value;
    const nombres = document.getElementById('nombres').value;
    const apellidos = document.getElementById('apellidos').value;
    const direccion_residencia = document.getElementById('direccion_residencia').value;
    const direccion_trabajo = document.getElementById('direccion_trabajo').value;
    const latitud_residencia = document.getElementById('latitud_residencia').value;
    const longitud_residencia = document.getElementById('longitud_residencia').value;
    const latitud_trabajo = document.getElementById('latitud_trabajo').value;
    const longitud_trabajo = document.getElementById('longitud_trabajo').value;

    // Enviar datos a la API
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cedula,
            nombres,
            apellidos,
            direccion_residencia,
            direccion_trabajo,
            latitud_residencia,
            longitud_residencia,
            latitud_trabajo,
            longitud_trabajo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estudiante agregado.');
            loadEstudiantes();
        } else {
            alert(`Error al agregar estudiante: ${data.error}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error al agregar el estudiante. Por favor, intente nuevamente.');
    });
});

function loadEstudiantes() {
    fetch('api.php?action=get')
    .then(response => response.json())
    .then(data => {
        const listaEstudiantes = document.getElementById('listaEstudiantes');
        listaEstudiantes.innerHTML = '';
        data.forEach(estudiante => {
            const li = document.createElement('li');
            li.textContent = `${estudiante.nombres} ${estudiante.apellidos} - ${estudiante.cedula}`;
            listaEstudiantes.appendChild(li);
        });
    })
    .catch(error => {
        console.error('Error al cargar estudiantes:', error);
    });
}
