#/usr/bin/python3

import sys
from .freeboxcontroller import FreeboxController
fbx = FreeboxController()
fbx.press(sys.argv[0])
pass
