import cx_Oracle

connection = cx_Oracle.connect('TODO', '123', 'localhost:1521')
print('Database version:', connection.version)
cursor = connection.cursor()

# cursor.execute (
#     """
#         CREATE TABLE Person(
#             person_id NUMBER GENERATED BY DEFAULT AS IDENTITY,
#             first_name VARCHAR2(50) NOT NULL,
#             last_name VARCHAR2(50) NOT NULL,
#             PRIMARY KEY(person_id)
#         )
#     """
# )

cursor.execute (
    """
    SELECT first_name, last_name
    FROM Person
    """
)

for fname, lname in cursor:
    print('Values:\t', fname, lname)

connection.close()