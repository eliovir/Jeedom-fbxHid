#! /usr/bin/env python3
# coding: utf-8
from remotefreebox.freeboxcontroller import FreeboxController
import sys
def main():
    fbx = FreeboxController()
    fbx.press(sys.argv[0])
    pass
if __name__ == "__main__":
    main()
