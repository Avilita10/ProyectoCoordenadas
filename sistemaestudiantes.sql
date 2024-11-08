CREATE DATABASE SistemaEstudiantes;
USE SistemaEstudiantes;

CREATE TABLE Estudiantes (
    cedula VARCHAR(20),
    nombres VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    ubicacion_trabajo point default null,
    ubicacion_casa point default null,
     PRIMARY KEY (cedula)
); 

