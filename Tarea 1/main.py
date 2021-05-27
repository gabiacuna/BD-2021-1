import cx_Oracle

def create_comuna(cursor, connection, comuna, id_comuna, pob, casos, id_region):
    try:
        casos, pob = int(casos), int(pob)

        #Se inserta en casos_por_comuna
        cursor.execute (
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados)""",[comuna,int(id_comuna), pob, casos]
        )
        
        #Se actualizan datos en casos_por_region
        select = 'SELECT * FROM CASOS_POR_REGION WHERE Codigo_region = ' + id_region
        cursor.execute(select)

        print('casos prev', casos)
        for i,r,c,p in cursor:  #id_region, region, casos, poblacion
            casos += c
            pob += p
        print('casos desp', casos)
        
        update = 'UPDATE CASOS_POR_REGION SET  Casos_confirmados = ' + str(casos)
        update += ', Poblacion = ' + str(pob) + ' WHERE Codigo_region = ' + id_region

        cursor.execute(update)

        #Se inserta la comuna nueva en comunas_por_region
        cursor.execute(
            'INSERT INTO COMUNAS_POR_REGION VALUES (:Codigo_region, :Region, :Codigo_comuna)', [int(id_region), r, int(id_comuna)]
        )

    except Exception as err:
        print('Hubo algun error creando la comuna:',err)
    else:
        print('Insercion completada')
        connection.commit()

def create_region(cursor, connection, region, id_region):
    try:
        #inserta en CASOS_POR_REGION
        cursor.execute(
            "INSERT INTO CASOS_POR_REGION VALUES (:Codigo_region, :Region, :Casos_confirmados, :Poblacion)", [int(id_region), region, 0, 0]
        )
    except Exception as err:
        print('Hubo algun error creando la región:',err)
    else:
        print('Insercion completada')
        connection.commit()


connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

menu = """
1....Crear comuna.
2....Crear region.
3....Ver casos totales de una comuna.
4....Ver casos totales de una region.
5....Ver Casos totales de todas las comunas.
6....Ver casos totales de todas las regiones.
7....Agregar casos nuevos a una comuna, es decir, aumentar los casos confirmados en n nuevos casos confirmados.
8....Eliminar casos nuevos a una comuna, es decir, disminuir los casos confirmados en n nuevos casos confirmados.
9....Combinar comunas.
10....Combinar regiones.
11....Top 5 comunas con mas porcentaje de casos segun su poblacion.
12....Top 5 regiones con mas porcentaje de casos segun su poblacion.

0....Salir
"""
print(menu)

opcion = input('\nIngrese la operacion a realizar:\t')

while opcion != '0':
    if opcion == '1':
        print('Ingrese los datos de la comuna a crear, con formato,')
        print('\t\tcomuna, codigo_comuna, poblacion, casos_confirmados, id_region')
        entrada = input('>>> ')

        comuna, id_comuna, pob, casos, id_region = entrada.split(', ')

        create_comuna(cursor, connection, comuna,id_comuna,pob,casos,id_region)
    elif opcion == '2':
        print('Ingrese los datos de la comuna a crear, con formato,')
        print('\t\tregion, codigo_region')
        entrada = input('>>> ')
        
        region, id_region = entrada.split(', ')

        create_region(cursor, connection, region, int(id_region))
    elif opcion == '3':
        entrada = input('Ingrese el codigo o nombre de la comuna a vizualizar:\t')
        if entrada[0] in '0123456789':
            select = 'SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = ' + entrada
            cursor.execute(select)
            print('llega aca')
            for n_comuna, id_comuna, pob, casos in cursor:
                porc = int(casos)/int(pob)
                print('En la comuna', n_comuna, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la comuna.')
        else:
            select = 'SELECT * FROM CASOS_POR_COMUNA WHERE Comuna = ' + entrada
            cursor.execute(select)
            print('llega aca')
            for n_comuna, id_comuna, pob, casos in cursor:
                porc = int(casos)/int(pob)
                print('En la comuna', n_comuna, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',porc*100,'% de la comuna.')
    
    #elif opcion == '4':

    opcion = input('\nIngrese la operacion a realizar:\t')

connection.close()
