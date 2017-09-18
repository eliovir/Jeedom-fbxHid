#/usr/bin/python3

import sys
from .freeboxcontroller import FreeboxController

print "Hello Python"

fbx = FreeboxController()
fbx.press(sys.argv[0])
pass
