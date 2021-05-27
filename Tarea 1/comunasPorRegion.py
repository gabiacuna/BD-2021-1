import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

cursor.execute (
    """
        CREATE TABLE COMUNAS_POR_REGION(
            Codigo_region INTEGER NOT NULL,
            Region VARCHAR2(50) NOT NULL,
            Codigo_comuna INTEGER NOT NULL,
            PRIMARY KEY(Codigo_comuna)
        )
    """
)

with open('RegionesComunas.csv') as regionesC_file:

    next(regionesC_file) #Para saltarse la primera linea con headers

    for line in regionesC_file:     #Region,Codigo Region,Codigo Comuna
        line = line.strip().split(',')
        region,id_region, id_comuna = line
        id_region, id_comuna = int(id_region), int(id_comuna)
        try:
            cursor.execute (
                """INSERT INTO COMUNAS_POR_REGION VALUES (:Codigo_region, :Region, :Codigo_comuna)""",[id_region,region, id_comuna ]
            )
        except Exception as err:
            print('Hubo algun error insertando datos:',err)
        else:
            print('Insercion completada')
            connection.commit()

connection.close()
