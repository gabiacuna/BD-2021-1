import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# cursor.execute("DROP TABLE CASOS_POR_COMUNA")
cursor.execute("DROP TABLE CASOS_POR_REGION")

cursor.execute (
    """
        CREATE TABLE CASOS_POR_REGION(
            Codigo_region INTEGER NOT NULL,
            Region VARCHAR2(50) NOT NULL,
            Casos_confirmados INTEGER NOT NULL,
            Poblacion INTEGER NOT NULL,
            Porcent FLOAT(5) DEFAULT 0,
            PRIMARY KEY(Codigo_region)
        )
    """
)

#Trigger que actualiza los porcentajes en la tabla CASOS_POR_REGION

cursor.execute(
    """
    CREATE OR REPLACE TRIGGER porcent_casos_region
        BEFORE UPDATE
        ON CASOS_POR_REGION
        FOR EACH ROW
        BEGIN
        :new.porcent := :new.casos_confirmados/:new.poblacion;
        END;
    """
)

#Trigger que para eliminar las comunas cada vez que se elimina una region

cursor.execute(
    """
    CREATE OR REPLACE TRIGGER borrado_region
    BEFORE DELETE ON CASOS_POR_REGION
    FOR EACH ROW
    BEGIN
        DELETE FROM CASOS_POR_COMUNA WHERE Codigo_region = :old.codigo_region;
    END;
    """
)

regiones = {} #id_region : nombre_region

with open('RegionesComunas.csv') as regionesC_file:

    next(regionesC_file) #Para saltarse la primera linea con headers

    for line in regionesC_file:     #Region,Codigo Region,Codigo Comuna
        line = line.strip().split(',')
        nombre, cod,_ = line
        if int(cod) not in regiones.keys():
            regiones[cod] = nombre

print(regiones)

try:
    for id_region, n_region in regiones.items():
        cursor.execute (
            """INSERT INTO CASOS_POR_REGION VALUES (:Codigo_region, :Region, 0, 0, 0)""",[id_region,n_region]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()

connection.close()
