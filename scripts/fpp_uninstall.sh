#!/bin/bash

#############################################################
## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
## Author: jessica12ryan                                   ##
## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
#############################################################
## Uninstall Script                                        ##
#############################################################

: "${FPPDIR:=/opt/fpp}"
. "${FPPDIR}/scripts/common"

setSetting restartFlag 1
