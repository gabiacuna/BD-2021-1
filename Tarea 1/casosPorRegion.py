import cx_Oracle

def id_region_para_comuna(cEnR, idComuna):
    for key, value in cEnR.items():
        if int(idComuna) in value:
            return key
    return -1

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

cursor.execute (
    """
        CREATE TABLE CASOS_POR_REGION(
            Codigo_region INTEGER NOT NULL,
            Region VARCHAR2(50) NOT NULL,
            Casos_confirmados INTEGER NOT NULL,
            Poblacion INTEGER NOT NULL,
            PRIMARY KEY(Codigo_region)
        )
    """
)

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

with open('CasosConfirmadosPorComuna.csv') as casosPorC_file:

    casosPorC = {} #dict[cod_comuna] = [info]

    next(casosPorC_file) #Para saltarse la primera linea con headers

    for line in casosPorC_file:
        line = line.strip().split(',')
        comuna,id_comuna, pob, casos = line
        id_region = id_region_para_comuna(comunas_en_region, id_comuna)
        if id_region != -1:
            casos_regiones[id_region][1] += int(casos)
            casos_regiones[id_region][2] += int(pob)

print(casos_regiones)
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
