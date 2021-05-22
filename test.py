from os import close
from typing import Tuple

insert = """
INSERT INTO Casos Por comuna (CODE, UNIT_NAME, GROUP_CODE, GROUP_NAME,)
VALUES(:1, :2, :3, :4)"""


with open('CasosConfirmadosPorComuna.csv') as casosPorC_file:

    casosPorC = [] #lista de tupla con los casos

    next(casosPorC_file) #Para saltarse la primera linea con headers

    for line in casosPorC_file:
        line = line.strip().split(',')
        casosPorC.append(tuple(line))

    print(casosPorC[0])
