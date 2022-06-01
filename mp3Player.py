#!/usr/bin/python3

import os
from os.path import exists
from pathlib import Path
from subprocess import call
from pynput.keyboard import Key, Controller, Listener
import subprocess
import threading
from omxplayer.player import OMXPlayer
from time import sleep
import RPi.GPIO as gpio
import signal
import numpy
import requests
import sqlite3
import socket
import json

gpio.setmode(gpio.BOARD)
gpio.setup(13, gpio.IN, pull_up_down=gpio.PUD_UP)
gpio.setup(15, gpio.OUT)

usbInserted = False
funcButton = False
continueLoop = True
readyToReceive = False
fileDuration = 0.0
keyboard = Controller()
myDir = "/home/pi/MP3s"
myDirOrig = myDir
myFile = ""
myFilePath = None
player = None
arr = os.listdir(myDir)
arr = sorted(arr)
arrPos = 0
arrPosList = [0]
current = arr[arrPos]
arrSize = len(arr)
lastFourCharacters = current[-4] + current[-3] + current[-2] + current[-1]
isFilePlaying = False
isFilePaused = False
continueProgram = True
input1 = ""
ignoreButtons = False
player = None
listener = None
lastInput = ""
card = -1
device = -1
oldCard = -1
oldDevice = -1
pressed = 0
enterThreadRunning = False
cardName = ""
flashLED = 0
allowFlash = True
flashing = True

try:
	f = open("mp3Player.txt","r")
	f.close()
except:
	f = open("mp3Player.txt","w+")
	f.write("text;0.0")
	f.close()
startPos = 0.0

os.system("pulseaudio -k")
os.system("pulseaudio -D")
#os.system("amixer cset numid=3 1")
current_volume = 50
os.system("amixer -q -M sset Master {0}%".format(current_volume))
omxVolume = 0.5

def cardHandler():
	global cardName
	global card
	global device
	global oldCard
	global oldDevice
	global flashing
	while True :
		sleep(2)
		aplaySub = str(subprocess.Popen("aplay -l", shell=True, stdout=subprocess.PIPE).stdout.read()).replace('\\n', '~')
		aplaySub_arr = aplaySub.split('~')
		if "USB" in aplaySub:
			for positionText in aplaySub_arr:
				if positionText[:4] == "card":
					if "USB" in positionText:
						positionText = positionText.replace('[', ']')
						splitText = positionText.split(']')
						card = int(splitText[0][5:splitText[0].find(':')])
						device = int(splitText[2][9:splitText[2].find(':')])
						cardName = splitText[0][splitText[0].find(':') + 2 : len(splitText[0]) - 1]
						oldCard = card if oldCard == -1 else oldCard
						oldDevice = device if oldDevice == -1 else oldDevice
		else:
			card = 0
			device = 0
			positionText = aplaySub_arr[1].replace('[', ']')
			splitText = positionText.split(']')
			cardName = splitText[0][splitText[0].find(':') + 2 : len(splitText[0]) - 1]
			oldCard = card if oldCard == -1 else oldCard
			oldDevice = device if oldDevice == -1 else oldDevice

		if card != oldCard or device != oldDevice:
			speak(cardName)
			flashing = False
			sleep (0.5)
			gpio.output(15, gpio.LOW)
			sleep(0.5)
			gpio.output(15, gpio.HIGH)
			sleep(0.15)
			gpio.output(15, gpio.LOW)
			sleep(0.15)
			gpio.output(15, gpio.HIGH)
			flashing = True
			oldCard = card
			oldDevice = device

cardHandlerThread = threading.Thread(target=cardHandler)
cardHandlerThread.start()

#os.system('xmodmap -e "keycode 123 = w"')
#os.system('xmodmap -e "keycode 122 = s"')

while card == -1 and device == -1:
	sleep(0.5)

sysPlayer = OMXPlayer(Path("/home/pi/Windows/Windows_NT_5_Startup_Sound.mp3"), args = ['-o', 'alsa:hw:{0},{1}'.format(card, device)])
sysPlayer.set_volume(0.1)
sleep(15)
gpio.output(15, gpio.HIGH)
sysPlayer.quit()
sysPlayer = None

def ledFlasher():
	global flashLED
	global allowFlash
	global flashing
	while allowFlash:
		sleep(1.5)
		if flashLED > 0.5 and allowFlash and flashing:
			while flashLED > 0.5 and allowFlash and flashing:
				flashLED = 0 if flashLED < 0 else flashLED
				gpio.output(15, gpio.LOW)
				sleep(flashLED * numpy.sign(flashLED) * allowFlash * flashing)
				gpio.output(15, gpio.HIGH)
				sleep(flashLED * numpy.sign(flashLED) * allowFlash * flashing)

ledFlasherThread = threading.Thread(target=ledFlasher)
ledFlasherThread.start()

def usbHandler():
	global usbInserted
	global text
	global isFilePlaying
	global flashLED
	global input1

	while True:
		sleep(1)

		if os.path.isdir("/sys/bus/usb/devices/1-1.3/"):
			if not usbInserted:
				if isFilePlaying == True:
					while isFilePlaying == True:
						sleep(1)
					sleep(7)

				flashLED += 1
				usbInserted = True

				dbconnect = sqlite3.connect("/home/pi/pi_mp3_player.db")
				dbconnect.row_factory = sqlite3.Row
				cursor = dbconnect.cursor()

				cursor.execute('''SELECT `pi_dir` FROM `file_locations` WHERE 1''')

				for row in cursor:
					if not exists(row['pi_dir']):
						cursor.execute('''DELETE FROM `file_locations` WHERE `pi_dir` = ?''', (row['pi_dir'],))
						dbconnect.commit()

				usbs = os.listdir("/media/pi")
				if len(usbs) > 0:
					
					speak("USB_Inserted")

					for element in usbs:
						files = os.listdir("/media/pi/{0}".format(element))
						if "MP3s" in files:
							speak("MP3_Found")
							#os.system("sudo cp -r /media/pi/{0}/MP3s /home/pi/MP3s".format(element))
							for root, dirs, fileNames in os.walk("/media/pi/{0}/MP3s".format(element)):
								for fileName in fileNames:
									if fileName.endswith(".mp3"):
										cursor.execute('SELECT COUNT(*) as `file_count` FROM `file_locations` WHERE `file_name` = ?', (fileName,))
										num = 0

										for row in cursor:
											num += int(row['file_count'])

										allowDownload = False
										alreadyExecuted = False

										newLoc = os.path.join(root, fileName).replace("/media/pi/{0}/MP3s".format(element), "/home/pi/MP3s")
										if num > 0 or exists(newLoc):
											if num == 0:
												cursor.execute('''INSERT INTO `file_locations` (`file_name`, `version`, `pi_dir`) 
													VALUES (?, ?, ?)''', (fileName, '1.0', newLoc,))
												dbconnect.commit()
												alreadyExecuted = True
											
											input1 = 'continue'
											speak('File_already_exists_do_you_wish_to_overwrite_it?')
											while input1 == 'continue':
												sleep(0.01)
											if input1 == 'w' or input1 == 'd':
												allowDownload = True
											else:
												speak("No_change_made_to_{0}".format(fileName))
										else:
											allowDownload = True

										if allowDownload:
											speak("adding_{0}".format(fileName.split("/")[-1]))

											os.system("sudo mkdir -p {0} && cp {1} {2}".format(os.path.join(root, "")
												.replace("/media/pi/{0}/MP3s".format(element), "/home/pi/MP3s"), 
												os.path.join(root, fileName), newLoc))

											if not alreadyExecuted:
												cursor.execute('''INSERT INTO `file_locations` (`file_name`, `version`, `pi_dir`) 
													VALUES (?, ?, ?)''', (fileName, '1.0', newLoc,))
												dbconnect.commit()

										sleep(0.25)

					os.system("sudo eject /dev/sda")
					sleep(5)
					flashLED -= 1
					speak("USB_Ejected")
				else:
					while os.path.isdir("/sys/bus/usb/devices/1-1.3/"):
						try:
							socket.getaddrinfo('google.com', 80)
							break
						except:
							sleep(10)

					try:
						requests.get("http://192.168.0.71/index.php/pi/online")
						domain = "http://192.168.0.71/index.php/pi/"
					except:
						try:
							requests.get("http://192.168.0.41/index.php/pi/online")
							domain = "http://192.168.0.41/index.php/pi/"
						except:
							domain = ""
					
					if domain != "":
						speak("Connected_to_Internet")

						cursor.execute('SELECT `file_name`, `version` FROM `file_locations`')
						rebootReqd = False

						names = ''
						versions = ''

						for row in cursor:
							names += '"' + row['file_name'] + '", '
							versions += '"' + str(row['version']) + '", '

						names = names[:-2]
						versions = versions[:-2]

						json_request = '{{"file_names": [{0}], "versions": [{1}]}}'.format(names, versions)
						
						headers = {'Accept' : 'application/json', 'Content-Type' : 'application/json'}
						r = requests.get(domain + "checkUpdate", data=json_request, headers=headers)

						missing_files = ''
						jsonLoads = ''
						if r.status_code == 200:
							jsonLoads = json.loads(r.content)['OK']
							missing_files = jsonLoads['Download']
						else:
							speak("Server_Error")
							flashLED -= 1
							continue

						for row in missing_files:
							speak("Updating_{0}".format(row['file_name']))

							if row['file_name'] == os.path.basename(__file__):
								rebootReqd = True
								os.system('sudo cp /home/pi/mp3Player.py /home/pi/mp3Player_last.py')

							directoryName = os.path.split(row['pi_dir'])[0]
							os.system("sudo mkdir -p {0}".format(directoryName))

							with open(row['pi_dir'], 'wb') as f:
								cursor.execute('''SELECT * FROM `file_locations` WHERE `file_name` = ?''', (row['file_name'],))

								response = requests.get(domain + "download?file_name={0}".format(row['file_name']), stream=True)
								total_length = response.headers.get('content-length')

								if response.status_code == 200:

									if len(cursor.fetchall()) == 0:
										cursor.execute('''INSERT INTO `file_locations` (`file_name`, `version`, `pi_dir`) 
											VALUES (?, ?, ?, ?)''', (
											row['file_name'],
											row['version'],
											row['pi_dir'],
										))
										dbconnect.commit()
									else:
										cursor.execute('''UPDATE `file_locations` SET `version` = ? WHERE `file_locations`.`file_name` = ?''', (
											row['version'],
											row['file_name'],
										))
										dbconnect.commit()

									deleteFile = False

									if total_length is None:
										f.write(response.content)
									else:
										save_count = 0
										for data in response.iter_content(chunk_size=4096):
											save_count += 1
											f.write(data)
											if save_count % 128 == 0:
												speak(str(save_count / 256))

								else:
									speak('{0}_Could_Not_Be_Updated'.format(row['file_name']))
									deleteFile = True
									f.write("FAILED DOWNLOAD".encode('utf-8'))
									dbconnect.commit()

							if deleteFile:
								os.system("sudo rm {0}".format(row['pi_dir']))

						speak("All_Files_Downloaded_And_Updated")

						extraFiles = jsonLoads['Upload']

						if len(extraFiles) > 0:
							speak("Uploading_Files_To_Server")

							for row in extraFiles:
								fileName = row['file_name']
								speak("Uploading_{0}".format(fileName))
								fileDir = cursor.execute('''SELECT `pi_dir` FROM `file_locations` WHERE `file_name` = ?''', (fileName,)).fetchall()['pi_dir']
								dataToSend = {'submit': 'true'}
								updateDb = 'true' if row['updt'] == 1 else 'false'
								params = {'vers': str(row['version']), 'pi_name': fileDir, 'update': updateDb}
								preparedFile = {'fileToUpload': open(fileDir, 'rb')}
								r = requests.post(domain + "upload", data=dataToSend, params=params, files=preparedFile)
								if r.status_code != 200:
									speak("Server_error_the_file_may_or_may_not_have_been_uploaded")

						flashLED -= 1

						if rebootReqd:
							dbconnect.close()
							speak("The_System_Will_Now_Reboot_Due_To_A_Script_Update")
							os.system('sudo reboot')
					else:
						flashLED -= 1
						speak("File_Transfer_Not_Possible")
						
				dbconnect.close()
		else:
			usbInserted = False

usbHandlerThread = threading.Thread(target=usbHandler)
usbHandlerThread.start()

def fileWrite():
	global isFilePlaying
	global isFilePaused
	global myFile
	global startPos

	while True:
		startPosition = startPos
		writeFile = False
		while isFilePlaying == True or isFilePaused == True:
			writeFile = True
			if isFilePlaying == True:
				sleep(0.1)
				startPosition += 0.1

		if writeFile == True:
			with open("mp3Player.txt","r+") as f:
				f.seek(0)
				if startPosition - 5.0 < 0.0:
					f.write(myFile + ";0.0")
				else:
					f.write(myFile + ";" + str(startPosition - 5.0))
				f.truncate()
				f.close()

fileWriteThread = threading.Thread(target=fileWrite)
fileWriteThread.start()

def on_press(key):
	try:
		keyStr = '{0}'.format(key.char)
	except AttributeError:
		keyStr = '{0}'.format(key)

def on_release(key):
	global input1
	global continueLoop
	global funcButton
	input = ""
	try:
		input = '{0}'.format(key.char)
	except AttributeError:
		input = '{0}'.format(key)

	while readyToReceive == False:
		continue

	if funcButton == True:
		continueLoop = False
		input = "a" if input == "s" else "d"

	input1 = input

def enterPress():
	global enterThreadRunning
	global pressed
	global input1
	global enterThread
	enterThreadRunning = True
	sleep(1)
	if pressed < 2:
		input1 = "Key.enter"
	else:
		input1 = ""
	pressed = 0
	enterThread = None
	enterThread = threading.Thread(target=enterPress)
	enterThreadRunning = False

enterThread = threading.Thread(target=enterPress)

def gpioHandler():
	global continueLoop
	global funcButton
	global input1
	global pressed
	global enterThread
	global enterThreadRunning
	while True:
		sleep(0.25)
		if gpio.input(13) == gpio.LOW and continueLoop:
			funcButton = True
			pressed += 1
			while continueLoop:
				if gpio.input(13) == gpio.HIGH:
					if enterThreadRunning == False:
						enterThread.start()
					break

		if gpio.input(13) == gpio.HIGH and funcButton == True:
			funcButton = False
			continueLoop = True

gpioHandlerThread = threading.Thread(target=gpioHandler)
gpioHandlerThread.start()

def speak(text):
	global cardName
	print(text)
	os.system("espeak -a {0} {1} --stdout | aplay -D 'sysdefault:CARD={2}'".format(current_volume, text, cardName))

speak(current)

shutdown = False

def waitForFileEnd():
	global player
	global isFilePlaying
	global isFilePaused
	global input1
	global lastInput

	while True:
		if isFilePlaying == True or isFilePaused == True:
			sleep(5)
			try:
				isPlaying = player.is_playing()
			except:
				while readyToReceive == False:
					continue
				if lastInput != "a":
					input1 = "a"

				sleep(2.5)

threadRunning = False

def userInterface():
	global usbInserted
	global lastInput
	global threadRunning
	global readyToReceive
	global fileDuration
	global shutdown
	global keyboard
	global volThread
	global text
	global player
	global myDir
	global myDirOrig
	global current_volume
	global myFile
	global arr
	global arrPos
	global current
	global arrSize
	global lastFourCharacters
	global isFilePlaying
	global isFilePaused
	global continueProgram
	global input1
	global ignoreButtons
	global omxVolume
	global wasFilePlaying
	global startPos
	global card
	global device
	global allowFlash

	changeVolume = False
	i = 0
	turnOffPlayer = False
	input1 = "continue"

	if isFilePlaying == True:
		changeVolume = True

	while i == 0:
		while input1 == "continue":
			readyToReceive = True
			continue

		readyToReceive = False
		lastInput = input1
		print(input1)

		if input1 == "Key.enter":
			if changeVolume == False:
				if isFilePlaying == False and isFilePaused == False and usbInserted == False:
					waitForSelect = True
					aplay = str(subprocess.Popen("aplay -l", shell=True, stdout=subprocess.PIPE).stdout.read()).replace('\\n', '~')
					aplay_arr = aplay.split('~')
					deviceArr = []
					for positionText in aplay_arr:
						if positionText[:4] == "card":
							positionText = positionText.replace('[', ']')
							splitText = positionText.split(']')
							splitText[0] = splitText[0][5:splitText[0].find(':')]
							splitText[2] = splitText[2][9:splitText[2].find(':')]
							splitText[1] = splitText[1].replace(' ', '_')
							deviceArr.append(splitText)
					maxIndex = len(deviceArr) - 1
					position = 1
					speak(deviceArr[position][1])
					input1 = "continue"
					readyToReceive = True
					while waitForSelect:
						if input1 == "s":
							position -= 1
							position = maxIndex if position < 0 else position
							speak(deviceArr[position][1])
							input1 = "continue"
						elif input1 == "w":
							position += 1
							position = 0 if position > maxIndex else position
							speak(deviceArr[position][1])
							input1 = "continue"
						elif input1 == "Key.enter":
							speak("{0}_selected".format(deviceArr[position][1]))
							card = int(deviceArr[position][0])
							device = int(deviceArr[position][2])
							waitForSelect = False
					input1 = "Key.enter"
					readyToReceive = False
				else:
					input1 = "no_input"
			else:
				input1 = "no_input"

		if input1 == "s" and usbInserted == False:
			if ignoreButtons == False:
				if changeVolume == True:
					current_volume_backup = current_volume
					if isFilePlaying == True or isFilePaused == True:
						current_volume = omxVolume * 100

					current_volume -= 2

					if current_volume < 0:
						current_volume = 0

					if isFilePlaying == True or isFilePaused == True:
						omxVolume = current_volume / 100
						current_volume = current_volume_backup
						player.set_volume(omxVolume)
					else:
						os.system("amixer -q -M sset Master " + str(current_volume) + "%")
						speak(str(current_volume))
				else:
					if isFilePlaying == False and isFilePaused == False:
						turnOffPlayer = False
						if arrPos + 1 == arrSize:
							arrPos = 0
						else:
							arrPos += 1

						current = arr[arrPos]
						arrPosList[-1] = arrPos
						speak(current)
					else:
						print('file playing')

		elif input1 == "w" and usbInserted == False:
			if ignoreButtons == False:
				if changeVolume == True:
					current_volume_backup = current_volume
					if isFilePlaying == True or isFilePaused == True:
						current_volume = omxVolume * 100

					current_volume += 2

					if current_volume > 100:
						current_volume = 100

					if isFilePlaying == True or isFilePaused == True:
						omxVolume = current_volume / 100
						current_volume = current_volume_backup
						player.set_volume(omxVolume)
					else:
						os.system("amixer -q -M sset Master " + str(current_volume) + "%")
						speak(str(current_volume))
				else:
					if isFilePlaying == False and isFilePaused == False:
						turnOffPlayer = False
						if arrPos == 0:
							arrPos = arrSize - 1
						else:
							arrPos -= 1

						current = arr[arrPos]
						arrPosList[-1] = arrPos
						speak(current)
					else:
						print('file playing')

		elif input1 == "a" and usbInserted == False:
			if ignoreButtons == False:
				changeVolume = False
				if myDir != myDirOrig:
					if isFilePaused == True:
						isFilePaused = False
						isFilePlaying = True
						player.play()
					del arrPosList[-1]
					if isFilePlaying == True or isFilePaused == True:
						player.quit()
						isFilePlaying = False
						isFilePaused = False
						wasFilePlaying = False
						myDir += "/ignore"
					arrPos = arrPosList[-1]
					turnOffPlayer = False
					myDir = myDir.rsplit("/", 1)[0]
					arr = os.listdir(myDir)
					arr = sorted(arr)
					current = arr[arrPos]
					arrSize = len(arr)
					lastFourCharacters = current[-4] + current[-3] + current[-2] + current[-1]
					speak(current)

				else:
					if turnOffPlayer == True:
						if usbInserted:
							speak("Will_shut_down_after_USB_has_been_ejected")
							speak("Wait_until_USB_has_been_ejected_before_removing_it")
							while usbInserted:
								sleep(0.5)
						speak("Shutting_Down")
						speak("Do_not_remove_power_until_red_light_is_fully_off")
						i = 1
						allowFlash = False
						sleep(0.5)
						shutdown = True
					else:
						turnOffPlayer = True
						speak("Press_again_to_turn_off_player")

		elif input1 == "d" and usbInserted == False:
			if ignoreButtons == False:
				if isFilePlaying == True:
					player.pause()
					isFilePlaying = False
					isFilePaused = True
					changeVolume = False
				elif isFilePaused == True:
					player.play()
					isFilePlaying = True
					isFilePaused = False
					changeVolume = True
				else:
					turnOffPlayer = False
					arrPosList.append(0)
					if lastFourCharacters == ".mp3":
						startPos = 0.0
						myFile = myDir + "/" + current
						i = 1
						changeVolume = True
						f = open("mp3Player.txt","r")
						if f.mode == "r":
							f.seek(0)
							contents = f.read()
							contentList = contents.split(";")
							if contentList[0] == myFile:
								if float(contentList[1]) != 0.0:
									speak("Do_you_wish_to_resume?")
									input1 = "continue"
									while True:
										readyToReceive = True
										if input1 == "w":
											startPos = float(contentList[1])
											break
										elif input1 == "s":
											startPos = 0.0
											break
									readyToReceive = False
						f.close()
						if threadRunning == False:
							waitEnd = threading.Thread(target=waitForFileEnd)
							waitEnd.daemon = True
							waitEnd.start()
							threadRunning = True
					else:
						changeVolume = False
						myDir += "/" + current
						arr = os.listdir(myDir)
						arr = sorted(arr)
						arrPos = 0
						current = arr[arrPos]
						arrSize = len(arr)
						lastFourCharacters = current[-4] + current[-3] + current[-2] + current[-1]
						speak(current)

		elif input1 ==  "" and usbInserted == False:
			if isFilePlaying == False:
				if changeVolume == False:
					changeVolume = True
					speak("change_volume")
				else:
					changeVolume = False
					speak("volume_set")

		elif input1 != "Key.enter" and usbInserted == False:
			if ignoreButtons == False:
				turnOffPlayer = False
				speak("Error")

		input1 = "continue"

	i = 0

def continueProgramFunc():
	global isFilePlaying
	global wasFilePlaying
	global myFilePath
	global myFile
	global player
	global text
	global shutdown
	global startPos

	while continueProgram == True:
		userInterface()
		if shutdown == True:
			gpio.output(15, gpio.LOW)
			systemPlayer = OMXPlayer(Path("/home/pi/Windows/Windows_NT_5_Shutdown_Sound.mp3"), args = ['-o', 'alsa:hw:{0},{1}'.format(card, device)])
			systemPlayer.set_volume(0.075)
			sleep(5)
			subprocess.Popen(['shutdown','-h','now'])
		else:
			myFilePath = Path(myFile)
			isFilePlaying = True
			speak("playing")

			os.system("sudo rm /tmp/*")
			player = OMXPlayer(Path(myFile), args = ['-o', 'alsa:hw:{0},{1}'.format(card, device)])
			player.set_volume(omxVolume)
			player.set_position(startPos)
			wasFilePlaying = True

continueProgramFuncThread = threading.Thread(target=continueProgramFunc)
continueProgramFuncThread.start()
with Listener(on_press=on_press, on_release=on_release) as listener:
	listener.join()