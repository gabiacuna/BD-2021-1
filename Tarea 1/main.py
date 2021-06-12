import cx_Oracle

def check_15(cursor, connection, pob, casos, id_region):    #Revisa que si se agregan pob y casos a una region siga teniendo positividad <0.15
    casos, pob, id_region = int(casos), int(pob), int(id_region)

    cursor.execute("SELECT Region, Casos_confirmados, poblacion FROM CASOS_POR_REGION WHERE Codigo_region = :1",[id_region])

    region, casos_region, pob_region = cursor.fetchall()[0]
    print(region)
    
    new_poc = (casos_region + casos)/(pob_region + pob)
    if new_poc > 0.15:
        
        try: 
            cursor.execute("DELETE FROM CASOS_POR_REGION WHERE Codigo_region = :1", [id_region])
        except Exception as err:
            print('Hubo algun error borrando la región:',err)
        else:
            print('############################ ~ Advertencia ~ ############################')
            print('~   Se extirpara de la nación a la región', region, ' ~')
            print('~   Ya que esta cuenta con una positividad regional de', round(new_poc,2) , '%  ~')
            print('#########################################################################')
            connection.commit()
            return False
    else:
        return True

def create_comuna(cursor, connection, comuna, id_comuna, pob, casos, id_region):
    try:
        casos, pob, id_region = int(casos), int(pob), int(id_region)

        menor15 = check_15(cursor, connection, pob, casos, id_region)

        if menor15:
            #Se inserta en casos_por_comuna
            cursor.execute (
                """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados, :Codigo_region,0)""",[comuna,int(id_comuna), pob, casos, int(id_region)]
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
            "INSERT INTO CASOS_POR_REGION VALUES (:Codigo_region, :Region, :Casos_confirmados, :Poblacion,0)", [int(id_region), region, 0, 0]
        )
    except Exception as err:
        print('Hubo algun error creando la región:',err)
    else:
        print('Insercion completada')
        connection.commit()

def mod_cant_casos_comuna(cursor, id_comuna, n):
    cursor.execute("SELECT Casos_confirmados, Codigo_region FROM CASOS_POR_COMUNA WHERE Codigo_comuna = :id",[id_comuna])

    for casos, id_region in cursor:
        new_casos = int(casos) + int(n)
        update = 'UPDATE CASOS_POR_COMUNA SET Casos_confirmados = ' + str(new_casos)
        update +=' WHERE Codigo_comuna = ' + id_comuna
    
    try:
        cursor.execute(update)

        check_15(cursor, connection, 0, 0, id_region)

    except Exception as err:
        print('Hubo algun error modificando casos de la Comuna:', err)
        return'Hubo algun error'
    else:
        connection.commit()
        return 'Actualización completada'

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# Creación Views:

cursor.execute(
    """
    CREATE OR REPLACE VIEW vista_all_comunas AS
        SELECT comuna, casos_confirmados FROM CASOS_POR_COMUNA ORDER BY Codigo_region
    """
)

cursor.execute(
    """
    CREATE OR REPLACE VIEW vista_all_regiones AS
        SELECT region, casos_confirmados FROM CASOS_POR_REGION ORDER BY Codigo_region
    """
)

cursor.execute(
    """
    CREATE OR REPLACE VIEW vista_porc_comunas AS
        SELECT comuna, porcent FROM CASOS_POR_COMUNA ORDER BY porcent DESC
    """
)

cursor.execute(
    """
    CREATE OR REPLACE VIEW vista_porc_regiones AS
        SELECT region, porcent FROM CASOS_POR_REGION ORDER BY porcent DESC
    """
)

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
        entrada = input('Ingrese el codigo de la comuna a vizualizar:\t')
        
        cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = :1", [int(entrada)])

        n_comuna, id_comuna, pob, casos, id_region, porc = cursor.fetchall()[0]
            
        print('En la comuna', n_comuna, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la comuna.')
          
    elif opcion == '4':
        entrada = input('Ingrese el codigo de la región a vizualizar:\t')
        if entrada[0] in '0123456789':  #En caso de que el user ingrese el id de la region
            cursor.execute("SELECT region, casos_confirmados, poblacion, porcent FROM CASOS_POR_REGION WHERE Codigo_region = :1",[int(entrada)])

            for nombre, casos, pob, porc in cursor:

                print(nombre, 'AKK')

                print('En la región', nombre, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la región.')
        
    elif opcion == '5':
        cursor.execute("""SELECT * FROM vista_all_comunas """)

        print('\tComuna\t\t|\tCasos\t')
        for comuna, casos in cursor:
            print(' ', comuna, '\t\t', casos)
    
    elif opcion == '6':

        cursor.execute("""SELECT * FROM vista_all_regiones """)

        print('\tRegion\t\t|\tCasos\t')
        for region, casos in cursor:
            print(' ', region, '\t\t', casos)
    
    elif opcion == '7':
        print('Ingrese el numero de casos a aumentar y el código de la comuna a la que pertenecen (n, id):')
        entrada = input('>>> ')
        n, id_comuna = entrada.split(', ')

        res = mod_cant_casos_comuna(cursor, id_comuna, n)
        print(res)
    
    elif opcion == '8':
        print('Ingrese el numero de casos a disminuir y el código de la comuna a la que pertenecen (n, id):')
        entrada = input('>>> ')
        n, id_comuna = entrada.split(', ')

        n = '-' + n

        res = mod_cant_casos_comuna(cursor, id_comuna, n)

        print(res)

    elif opcion == '9':
        id_comuna1 = input('Ingrese el Codigo de la primera comuna a combinar:\n>>> ')
        id_comuna2 = input('Ingrese el Codigo de la segunda comuna a combinar:\n>>> ')

        id_comuna1 = id_comuna1.strip()

        cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna1)

        nombre_c1, _, pob1, casos1, id_r1, porc= cursor.fetchall()[0]
        
        cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna2)

        nombre_c2, _, pob2, casos2, id_r2, porc = cursor.fetchall()[0]

        if id_r1 != id_r2:
            r_objet = input('En que Region desea dejar la comuna nueva? [1 o 2]\n>>> ')
            r_objet = id_r1 if r_objet == '1' else id_r2
            id_comunaNew = id_comuna1 if r_objet == '1' else id_comuna2
        else:
            r_objet = id_r1
            id_comunaNew = id_comuna1

        nombre_cnew = input('Ingrese el nombre de la comuna nueva:\n>>>')
       
        #Borrado de las comunas:

        cursor.execute("DELETE FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna1)
        cursor.execute("DELETE FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna2)

        #Falta crear id para comuna nueva
            #Hacer count regiones, y el id sería: id_region + ???

        #Insercion comuna nueva:

        pob_new = int(pob1) + int(pob2)
        casos_new = int(casos1) + int(casos2)

        try:
            cursor.execute("INSERT INTO CASOS_POR_COMUNA(comuna,codigo_comuna,poblacion,casos_confirmados,codigo_region) VALUES(:1, :2, :3, :4, :5)", [nombre_cnew, id_comunaNew, pob_new, casos_new,r_objet])
        except Exception as err:
            print('Hubo algun error combinando las comunas:',err)
        else:
            print('Combinacion completada')
            check_15(cursor, connection, 0, 0, r_objet )
            connection.commit()

    elif opcion == '10':
        id_region1 = input('Ingrese el Codigo de la primera region a combinar:\n>>> ')
        id_region2 = input('Ingrese el Codigo de la segunda region a combinar:\n>>> ')

        if id_region1 == id_region2:
            print('Son los mismos códigos de Region!, ingrese regiones distintas :)')
            continue

        nombre_Rnew = input('Ingrese el nombre de la nueva region:\n>>> ')

        #Se usara el codigo de la region 1 para la nueva region

        update = 'UPDATE CASOS_POR_COMUNA SET Codigo_region = ' + id_region1 + ' WHERE Codigo_region = ' + id_region2

        cursor.execute(update)

        cursor.execute("DELETE FROM CASOS_POR_REGION WHERE Codigo_region = " + id_region2)

        try:
            cursor.execute("UPDATE CASOS_POR_REGION SET Region = :1 WHERE Codigo_region = :2", [nombre_Rnew, id_region1])
        
        except Exception as err:
            print('Hubo algun error combinando las comunas:',err)
        else:
            print('Combinacion completada')
            check_15(cursor, connection, 0,0,id_region1)
            connection.commit()
    
    elif opcion == '11':
        cursor.execute("SELECT * FROM vista_porc_comunas WHERE ROWNUM <= 5")
        print('\tComuna\t\t|\tCasos/Pob\t')
        for comuna, casos in cursor:
            print(' ', comuna, '\t\t', round(float(casos)*100,3), '%')
    
    elif opcion == '12':
        cursor.execute("SELECT * FROM vista_porc_regiones WHERE ROWNUM <= 5")
        print('\tRegion\t\t|\tCasos/Pob\t')
        for region, casos in cursor:
            print(' ', region, '\t\t', round(float(casos)*100,3), '%')


    opcion = input('\nIngrese la operacion a realizar:\t')

connection.close()
