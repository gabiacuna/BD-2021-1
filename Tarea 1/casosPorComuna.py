import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# cursor.execute("DROP TABLE CASOS_POR_COMUNA")

cursor.execute (
    """CREATE TABLE CASOS_POR_COMUNA(
            Comuna VARCHAR2 (50) NOT NULL,
            Codigo_comuna INTEGER NOT NULL,
            Poblacion INTEGER NOT NULL,
            Casos_confirmados INTEGER NOT NULL,
            Codigo_region INTEGER REFERENCES CASOS_POR_REGION(Codigo_region),
            Porcent FLOAT(3),
            PRIMARY KEY(Codigo_comuna)
        )
    """
)

#Trigger que actualiza a regiones cuando se le hace un insert a comunas
try:

    cursor.execute(
        """
        CREATE OR REPLACE TRIGGER insert_casos_region
        AFTER INSERT
        ON CASOS_POR_COMUNA
        FOR EACH ROW
        BEGIN 
            UPDATE CASOS_POR_REGION
            SET Casos_confirmados = Casos_confirmados + :new.casos_confirmados,
            poblacion = poblacion + :new.poblacion
            WHERE codigo_region = :new.codigo_region;        
        END;
        """
    )
except  Exception as err:
    print('Hubo algun error creando el trigger insert_casos_region:',err)
else:
    print('Trigger insert_casos_region creado')

#Trigger que actualiza a regiones cuando se hace un update a comunas
try:
    cursor.execute(
        """
        CREATE OR REPLACE TRIGGER update_casos_comuna
        AFTER UPDATE
        ON CASOS_POR_COMUNA
        FOR EACH ROW
        BEGIN 
            UPDATE CASOS_POR_REGION
            SET Casos_confirmados = Casos_confirmados - :old.casos_confirmados + :new.casos_confirmados,
            poblacion = poblacion - :old.poblacion + :new.poblacion
            WHERE codigo_region = :new.codigo_region;        
        END;
        """
    )
except  Exception as err:
    print('Hubo algun error creando el trigger update_casos_comuna:',err)
else:
    print('Trigger update_casos_comuna creado')



cursor.execute(
    """
    CREATE OR REPLACE TRIGGER porcent_casos_comun
        BEFORE INSERT OR UPDATE
        ON CASOS_POR_COMUNA
        FOR EACH ROW
        BEGIN
        :new.porcent := :new.casos_confirmados/:new.poblacion;
        END;
    """
)


with open('CasosConfirmadosPorComuna.csv') as casosPorC_file:

    casosPorC = {} #dict[cod_comuna] = [info]

    next(casosPorC_file) #Para saltarse la primera linea con headers

    for line in casosPorC_file:
        line = line.strip().split(',')
        comuna,id_comuna, pob, casosC = line
        casosPorC [id_comuna] = [comuna, pob, casosC]

with open('RegionesComunas.csv') as regionesC_file:

    next(regionesC_file) #Para saltarse la primera linea con headers

    for line in regionesC_file:     #Region,Codigo Region,Codigo Comuna
        line = line.strip().split(',')
        region,id_region, id_comuna = line

        casosPorC[id_comuna].append(id_region)

try:
    for id_comuna, line in casosPorC.items():
        comuna, pob, casos, id_region = line
        cursor.execute (    #Revidar como queda el cod_region
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados, :Codigo_region, 0)""",[comuna,int(id_comuna), int(pob), int(casos), int(id_region)]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()


connection.close()