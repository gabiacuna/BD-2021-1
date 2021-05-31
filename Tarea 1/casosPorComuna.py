import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

cursor.execute(
    """
        CREATE TABLE CASOS_POR_COMUNA(
            Comuna VARCHAR2(50) NOT NULL,
            Codigo_comuna INTEGER NOT NULL,
            Poblacion INTEGER NOT NULL,
            Casos_confirmados INTEGER NOT NULL,
            Codigo_region INTREGER NULL REFERENCES CASOS_POR_REGION(Codigo_region),
            PRIMARY KEY (Codigo_comuna),
            FOREGIN KEY (Codigo_region)
        )
    """
)

with open('CasosConfirmadosPorComuna.csv') as casosPorC_file:

    casosPorC = [] #lista de tupla con los casos

    next(casosPorC_file) #Para saltarse la primera linea con headers

    for line in casosPorC_file:
        line = line.strip().split(',')
        casosPorC.append(tuple(line))

try:
    for comuna,id_comuna, pob, casos in casosPorC:
        cursor.execute (    #Revidar como queda el cod_region
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados)""",[comuna,int(id_comuna), int(pob), int(casos)]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()


with open('RegionesComunas.csv') as regionesC_file:

    next(regionesC_file) #Para saltarse la primera linea con headers

    for line in regionesC_file:     #Region,Codigo Region,Codigo Comuna
        line = line.strip().split(',')
        region,id_region, id_comuna = line
        id_region, id_comuna = int(id_region), int(id_comuna)

        #### Hacer el update para agregar el cod_region a todas las comunas!!!!!
        # try:
        #     cursor.execute (
        #         """INSERT INTO COMUNAS_POR_REGION VALUES (:Codigo_region, :Region, :Codigo_comuna)""",[id_region,region, id_comuna ]
        #     )
        # except Exception as err:
        #     print('Hubo algun error insertando datos:',err)
        # else:
        #     print('Insercion completada')
        #     connection.commit()

connection.close()
connection.close()