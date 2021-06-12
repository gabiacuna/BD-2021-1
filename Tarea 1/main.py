import cx_Oracle

def check_15(cursor, connection, id_region):    #Revisa que si se agregan pob y casos a una region siga teniendo positividad <0.15
    id_region = int(id_region)

    cursor.execute("SELECT Region, porcent FROM CASOS_POR_REGION WHERE Codigo_region = :1",[id_region])

    region, new_poc = cursor.fetchall()[0]

    if new_poc > 0.15:
        
        try: 
            cursor.execute("DELETE FROM CASOS_POR_REGION WHERE Codigo_region = :1", [id_region])
        except Exception as err:
            print('Hubo algun error borrando la región:',err)
        else:
            print('############################ ~ Advertencia ~ ############################')
            print('\t~   Se extirpara de la nación a la región', region, ' ~')
            print('\t~   Ya que esta cuenta con una positividad regional de', round(new_poc,2) , '%  ~')
            print('#########################################################################')
            connection.commit()
            return False
    else:
        return True

def create_comuna(cursor, connection, comuna, id_comuna, pob, casos, id_region):
    try:
        id_comuna, casos, pob, id_region = int(id_comuna), int(casos), int(pob), int(id_region)

        #Se inserta en casos_por_comuna
        cursor.execute (
            """INSERT INTO CASOS_POR_COMUNA(Comuna, Codigo_comuna, Poblacion, Casos_confirmados, Codigo_region) VALUES (:1, :2, :3, :4, :5)""",[comuna,id_comuna, pob, casos, id_region]
        )

    except Exception as err:
        print('Hubo algun error creando la comuna:',err)
    else:
        print('Insercion completada')
        connection.commit()
        #revisa si se mantiene la positividad regional menor a 15
        check_15(cursor, connection, id_region)

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
        #revisa si se mantiene la positividad regional menor a 15
        check_15(cursor, connection, id_region)

#Aumenta o disminuye en n los casos_confirmados de la comuna id_comuna
def mod_cant_casos_comuna(cursor, connection, id_comuna, n):
    try:
        cursor.execute("SELECT Casos_confirmados, Codigo_region FROM CASOS_POR_COMUNA WHERE Codigo_comuna = :id",[id_comuna])

        casos, id_region = cursor.fetchall()[0]

        new_casos = int(casos) + int(n) #se calcula la nueva cantidad de casos.

        cursor.execute('UPDATE CASOS_POR_COMUNA SET Casos_confirmados = :1 WHERE Codigo_comuna = :2', [new_casos, id_comuna])


    except Exception as err:
        print('Hubo algun error modificando casos de la Comuna:', err)
        return False
    else:
        connection.commit()
        check_15(cursor, connection, id_region)
        return 'Actualización completada'

#Conex

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# Creación Views:

#Para ver casos por comuna con nombre y cantidad de casos
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

#Para ver top comunas con mayor positividad
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
m....Ver menu
"""

print(menu)

opcion = input('\nIngrese la operacion a realizar:\t')

while opcion != '0':
    if opcion == '1':
        print('Ingrese los datos de la comuna a crear, con formato,')
        print('\t\tcomuna, codigo_comuna, poblacion, casos_confirmados, id_region')
        entrada = input('>>> ')

        comuna, id_comuna, pob, casos, id_region = entrada.split(', ')  #extraccion de datos de entrada.

        create_comuna(cursor, connection, comuna,id_comuna,pob,casos,id_region)

    elif opcion == '2':
        print('Ingrese los datos de la comuna a crear, con formato,')
        entrada = input('\t\tregion, codigo_region\n>>> ')
        
        region, id_region = entrada.split(', ')

        create_region(cursor, connection, region, int(id_region))

    elif opcion == '3':
        entrada = input('Ingrese el codigo de la comuna a vizualizar:\t')

        try:
            #Extraccion de los datos de la BD
            cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = :1", [int(entrada)])
            n_comuna, id_comuna, pob, casos, id_region, porc = cursor.fetchall()[0]

        except Exception as err:
            print('Hubo un error extrayendo los datos de CASOS_POR_COMUNA: ', err)
        else:                
            print('En la comuna', n_comuna, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la comuna.')
          
    elif opcion == '4':
        entrada = input('Ingrese el codigo de la región a vizualizar:\t')

        try:
            cursor.execute("SELECT region, casos_confirmados, poblacion, porcent FROM CASOS_POR_REGION WHERE Codigo_region = :1",[int(entrada)])
        except Exception as err:
            print('Hubo un error extrayendo los datos de CASOS_POR_REGION: ', err)
        else:
            for nombre, casos, pob, porc in cursor:

                print('En la región', nombre, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la región.')
        
    elif opcion == '5':
        #Utilizacion de la vista vista_all_comunas
        try:
            cursor.execute("""SELECT * FROM vista_all_comunas """)
        except Exception as err:
            print('Hubo un error utilizando la vsta vista_all_comunas: ', err)
        else:
            print('\tComuna\t\t|\tCasos\t')
            for comuna, casos in cursor:
                print(' ', comuna, '\t\t', casos)
        
    elif opcion == '6':
        try:
            cursor.execute("""SELECT * FROM vista_all_regiones """)
        except Exception as err:
            print('Hubo un error utilizando la vsta vista_all_regiones: ', err)
        else:
            print('\tRegion\t\t|\tCasos\t')
            for region, casos in cursor:
                print(' ', region, '\t\t', casos)
    
    elif opcion == '7':
        print('Ingrese el numero de casos a aumentar y el código de la comuna a la que pertenecen (n, id):')
        entrada = input('>>> ')
        n, id_comuna = entrada.split(', ')
        
        res = mod_cant_casos_comuna(cursor, connection, id_comuna, n)   #Funcion que agrega los n casos a la cumuna correspondiente

        if res:
            print(res)
    
    elif opcion == '8':
        print('Ingrese el numero de casos a disminuir y el código de la comuna a la que pertenecen (n, id):')
        entrada = input('>>> ')
        n, id_comuna = entrada.split(', ')

        n = '-' + n

        res = mod_cant_casos_comuna(cursor, connection, id_comuna, n)   #Funcion que quita los n casos a la cumuna correspondiente

        if res:
            print(res)

    elif opcion == '9':
        id_comuna1 = input('Ingrese el Codigo de la primera comuna a combinar:\n>>> ')
        id_comuna2 = input('Ingrese el Codigo de la segunda comuna a combinar:\n>>> ')

        id_comuna1 = id_comuna1.strip()

        try:
            #Extraccion de datos de las comunas a unir:
            cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna1)

            nombre_c1, _, pob1, casos1, id_r1, porc= cursor.fetchall()[0]
            
            cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna2)

            nombre_c2, _, pob2, casos2, id_r2, porc = cursor.fetchall()[0]

            #Selección de datos para la nueva comuna (que es la suma de las anteriores)
            if id_r1 != id_r2:
                eleccion = input('En que Region desea dejar la comuna nueva? [1 o 2]\n>>> ')
                id_r_obj = id_r1 if eleccion == '1' else id_r2
                id_comunaNew = id_comuna1 if eleccion == '1' else id_comuna2
            else:
                id_r_obj = id_r1
                id_comunaNew = id_comuna1

            nombre_cnew = input('Ingrese el nombre de la comuna nueva:\n>>>')
        
            #Borrado de las comunas a combinar:

            cursor.execute("DELETE FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna1)
            cursor.execute("DELETE FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna2)

            #Insercion comuna nueva:

            pob_new = int(pob1) + int(pob2)
            casos_new = int(casos1) + int(casos2)
        
            cursor.execute("INSERT INTO CASOS_POR_COMUNA(comuna,codigo_comuna,poblacion,casos_confirmados,codigo_region) VALUES(:1, :2, :3, :4, :5)", [nombre_cnew, id_comunaNew, pob_new, casos_new,id_r_obj])
        except Exception as err:
            print('Hubo algun error combinando las comunas:',err)
        else:
            print('Combinacion completada, la comuna resultante se llama', nombre_cnew, 'con Codigo de comuna,', id_comunaNew)
            connection.commit()
            
            check_15(cursor, connection, id_r_obj)

    elif opcion == '10':

        #recoleccion de datos de las comunas a combinar
        id_region1 = input('Ingrese el Codigo de la primera region a combinar:\n>>> ')
        id_region2 = input('Ingrese el Codigo de la segunda region a combinar:\n>>> ')

        if id_region1 == id_region2:
            print('Son los mismos códigos de Region!, ingrese regiones distintas :)')
            continue

        nombre_Rnew = input('Ingrese el nombre de la nueva region:\n>>> ')

        try:
            #Se usara el codigo de la region 1 para la nueva region (para asegurarnos de no repetir PK)

            cursor.execute("UPDATE CASOS_POR_COMUNA SET Codigo_region = :1  WHERE Codigo_region = :2", [id_region1, id_region2])    #Ahora las comunas de la region 2 pertenecen a la region 1

            cursor.execute("DELETE FROM CASOS_POR_REGION WHERE Codigo_region = " + id_region2)  #Se elimina la comuna 2 (todas sus comunas ahora)

            cursor.execute("UPDATE CASOS_POR_REGION SET Region = :1 WHERE Codigo_region = :2", [nombre_Rnew, id_region1])
        
        except Exception as err:
            print('Hubo algun error combinando las comunas:',err)
        else:
            print('Combinacion completada')
            connection.commit()

            check_15(cursor, connection, id_region1)
    
    elif opcion == '11':
        try:
            cursor.execute("SELECT * FROM vista_porc_comunas WHERE ROWNUM <= 5")
        except Exception as err:
            print('Hubo un error accediendo a la vista vista_porc_comunas: ', err)
        else:
            print('\tComuna\t\t|\tPositividad\t')
            for comuna, casos in cursor:
                print(' ', comuna, '\t\t', round(float(casos)*100,3), '%')
    
    elif opcion == '12':
        try:
            cursor.execute("SELECT * FROM vista_porc_regiones WHERE ROWNUM <= 5")
        except Exception as err:
            print('Hubo un error accediendo a la vista vista_porc_regiones: ', err)
        else:
            print('\tRegion\t\t|\tPositividad\t')
            for region, casos in cursor:
                print(' ', region, '\t\t', round(float(casos)*100,3), '%')
    
    elif opcion == 'm': #Para imprimir el menú de nuevo
        print(menu)

    opcion = input('\nIngrese la operacion a realizar:\t')

connection.close()
