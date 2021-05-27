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
            PRIMARY KEY (Codigo_comuna)
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
        cursor.execute (
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados)""",[comuna,int(id_comuna), int(pob), int(casos)]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()

connection.close()