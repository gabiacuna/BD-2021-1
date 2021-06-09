import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

cursor.execute("DROP TABLE CASOS_POR_COMUNA")

cursor.execute (
    """CREATE TABLE CASOS_POR_COMUNA(
            Comuna VARCHAR2 (50) NOT NULL,
            Codigo_comuna INTEGER NOT NULL,
            Poblacion INTEGER NOT NULL,
            Casos_confirmados INTEGER NOT NULL,
            Codigo_region INTEGER REFERENCES CASOS_POR_REGION(Codigo_region),
            PRIMARY KEY(Codigo_comuna)
        )
    """
)

# '''
# CONSTRAINT [symbol]] FOREIGN KEY
#     [index_name] (col_name, ...)
#     REFERENCES tbl_name (col_name,...)
#     [ON DELETE reference_option]
#     [ON UPDATE reference_option]
# '''

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
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados, :Codigo_region)""",[comuna,int(id_comuna), int(pob), int(casos), int(id_region)]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()


connection.close()