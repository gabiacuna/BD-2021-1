import cx_Oracle

voc_tilde = ['Ã¡', 'é', 'Ã\xad', 'ó', 'ú']
voc_sT = 'aeiou'

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

cursor.execute("DROP TABLE CASOS_POR_COMUNA")
cursor.execute("DROP TABLE CASOS_POR_REGION")

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

#Trigger que revisa que los casos sean menores al 15% de la población

# cursor.execute(
#     """
#     CREATE OR REPLACE TRIGGER trigger_casos_region_15
#     BEFORE INSERT OR UPDATE
#     ON CASOS_POR_REGION
#     FOR EACH ROW
#     BEGIN
#         IF :new.poblacion <> 0 AND :new.casos_confirmados / :new.poblacion > 0.15 THEN
#             :new.casos_confirmados := -1;
#             :new.codigo_region := -1;
#         END IF;
#     END trigger_casos_region_15;
#     """
# )

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
            """INSERT INTO CASOS_POR_REGION VALUES (:Codigo_region, :Region, 0, 0)""",[id_region,n_region]
        )
except Exception as err:
    print('Hubo algun error insertando datos:',err)
else:
    print('Insercion completada')
    connection.commit()

connection.close()
