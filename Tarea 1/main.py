import cx_Oracle

def create_comuna(cursor, connection, comuna, id_comuna, pob, casos, id_region):
    try:
        casos, pob = int(casos), int(pob)

        #Se inserta en casos_por_comuna
        cursor.execute (
            """INSERT INTO CASOS_POR_COMUNA VALUES (:Comuna, :Codigo_comuna, :Poblacion, :Casos_confirmados, :Codigo_region)""",[comuna,int(id_comuna), pob, casos, int(id_region)]
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
    
def casos_region(cursor):
    regiones = {}
    cursor.execute("SELECT * FROM CASOS_POR_REGION")

    for id_r, nombre in cursor:
        regiones[id_r] = [nombre, 0]
    
    cursor.execute("""SELECT Casos_confirmados, Codigo_region FROM CASOS_POR_COMUNA""")

    for casos, cod_r in cursor:
        regiones[cod_r][1] += casos
    return regiones

def mod_cant_casos_comuna(cursor, id_comuna, n):
    cursor.execute("SELECT Casos_confirmados FROM CASOS_POR_COMUNA WHERE Codigo_comuna = :id",[id_comuna])

    for casos in cursor:
        new_casos = int(casos[0]) + int(n)
        update = 'UPDATE CASOS_POR_COMUNA SET Casos_confirmados = ' + str(new_casos)
        update +=' WHERE Codigo_comuna = ' + id_comuna
    
    try:
        cursor.execute(update)
    except Exception as err:
        print('Hubo algun error creando agregando casos a la Comuna:', err)
        return'Hubo algun error'
    else:
        connection.commit()
        return 'Actualización completada'

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

#Creación Trigger, este revisa que se mantenga la regla de casos <= 15% de la pob total por región.

cursor.execute(
    """
    CREATE OR REPLACE TRIGGER trigger_casos_region_15
    BEFORE INSERT OR UPDATE
    ON CASOS_POR_REGION
    FOR EACH ROW
    BEGIN
        IF :new.poblacion <> 0 AND :new.casos_confirmados / :new.poblacion > 0.15 THEN
            :new.casos_confirmados := -1;
            :new.poblacion := -1;
        END IF;
    END trigger_casos_region_15;
    """
)

# Creación Views:

cursor.execute(
    """
    CREATE OR REPLACE VIEW vista_all_comunas AS
        SELECT comuna, casos_confirmados FROM CASOS_POR_COMUNA ORDER BY Codigo_comuna
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
        if entrada[0] in '0123456789':  #En caso de que el user ingrese el id de la comuna
            select = 'SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = ' + entrada
            cursor.execute(select)

            for n_comuna, id_comuna, pob, casos, id_region in cursor:
                porc = int(casos)/int(pob)
                print('En la comuna', n_comuna, 'de los', pob, 'habitantes,', casos,'estan con Covid-21. Esto es un',round(porc*100),'% de la comuna.')
          
    elif opcion == '4':
        entrada = input('Ingrese el codigo de la región a vizualizar:\t')
        if entrada[0] in '0123456789':  #En caso de que el user ingrese el id de la region
            select = 'SELECT poblacion,casos_confirmados FROM CASOS_POR_COMUNA WHERE Codigo_region = ' + entrada
            
            casos_region = 0
            pob_region = 0
            
            cursor.execute(select)

            for  pob, casos in cursor:
                casos_region += casos
                pob_region += pob

            cursor.execute("SELECT * FROM CASOS_POR_REGION WHERE Codigo_region = :1", [entrada])

            for id_region, nombre in cursor:
                
                porc = int(casos_region)/int(pob_region)
                print('En la región', nombre, 'de los', pob_region, 'habitantes,', casos_region,'estan con Covid-21. Esto es un',round(porc*100),'% de la región.')
        
    elif opcion == '5':
        cursor.execute("""SELECT * FROM vista_all_comunas """)

        print('\tComuna\t\t|\tCasos\t')
        for comuna, casos in cursor:
            print(' ', comuna, '\t\t', casos)
    
    elif opcion == '6':

        regiones = casos_region(cursor)
            
        print('\tRegion\t\t|\tCasos\t')
        for id_r, data in regiones.items():
            region, casos = data 
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
        id_comuna1 = input('Ingrese el Codigo de la primera comuna a combinar:\n>>>')
        id_comuna2 = input('Ingrese el Codigo de la segunda comuna a combinar:\n>>>')

        id_comuna1 = id_comuna1.strip()

        cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna1)

        nombre_c1, _, pob1, casos1, id_r1 = cursor.fetchall()[0]
        
        cursor.execute("SELECT * FROM CASOS_POR_COMUNA WHERE Codigo_comuna = " + id_comuna2)

        nombre_c2, _, pob2, casos2, id_r2 = cursor.fetchall()[0]

        if id_r1 != id_r2:
            r_objet = input('En que Region desea dejar la comuna nueva? [1 o 2]\n>>>')
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
            cursor.execute("INSERT INTO CASOS_POR_COMUNA VALUES(:1, :2, :3, :4, :5)", [nombre_cnew, id_comunaNew, pob_new, casos_new,r_objet])
        
        except Exception as err:
            print('Hubo algun error combinando las comunas:',err)
        else:
            print('Combinacion completada')
            connection.commit()

        

        




    opcion = input('\nIngrese la operacion a realizar:\t')

connection.close()
