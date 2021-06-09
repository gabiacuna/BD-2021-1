import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# cursor.execute (
#     """
#         CREATE TABLE CASOS_POR_REGION(
#             Codigo_region INTEGER NOT NULL,
#             Region VARCHAR2(50) NOT NULL,
#             Casos_confirmados INTEGER NOT NULL,
#             Poblacion INTEGER NOT NULL,
#             PRIMARY KEY(Codigo_region)
#         )
#     """
# )

casos_regiones = {} #id_region : [region,casos,poblacion]
comunas_en_region = {} #id_region : [comuna,comuna2...]

with open('RegionesComunas.csv') as regionesC_file:

    next(regionesC_file) #Para saltarse la primera linea con headers

    for line in regionesC_file:     #Region,Codigo Region,Codigo Comuna
        line = line.strip().split(',')
        if int(line[1]) not in casos_regiones.keys():
            casos_regiones[int(line[1])] = [line[0],0,0]

        if int(line[1]) not in comunas_en_region.keys():
            comunas_en_region[int(line[1])] = []
        
        comunas_en_region[int(line[1])].append(int(line[2]))

for region in casos_regiones.keys():
    for comuna in comunas_en_region[region]:
        sel = "SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + str(comuna)

        cursor.execute(sel)

        for _, id_comuna,pob,casos in cursor:
    
            casos_regiones[region][1] += casos
            casos_regiones[region][2] += pob

try:
    for id_region, lista in casos_regiones.items():
        region, casos, pob = lista
        cursor.execute (
            """INSERT INTO CASOS_POR_REGION VALUES (:Codigo_region, :Region, :Casos_confirmados, :Poblacion)""",[id_region,region, casos, pob ]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()

connection.close()
