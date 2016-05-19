#!/usr/bin/env python
import tika
tika.initVM()
from tika import parser
import requests
import io
import os

rootDir = 'crawl_data'
text_file = io.open("big.txt", "a", encoding='utf8')
for dirName, subdirList, fileList in os.walk(rootDir):
	print('Found directory: %s' %dirName)
	for fname in fileList:
		try:
			parsed = parser.from_file('crawl_data/' + fname)
			if("content" in parsed):
				#print("key exists!")
				text_file.write(parsed["content"])
		except TypeError:
			print("HEllo")
text_file.close()





with open("big.txt", "r") as f:
    for line in f:
        cleanedLine = line.strip()
        if cleanedLine: # is not empty
            print(set(cleanedLine))

