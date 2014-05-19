#!BPY

"""
Name: 'Data Plotter'
Blender: 248
Group: 'Misc'
Tooltip: 'Plots data from a CSV file (experimental).'
"""

__author__ = 'Zac Hester'
__version__ = '0.0.20100405'
__bpydoc__ = """ Experimental Data Plotting System

This script is my first attempt at a Blender plugin script.  I often
need to plot data sets on nice 3D charts.  Most charting applications
seem to render terrible/kludgey 3D representations that don't really
help illustrate the data any better than the 2D representation.

The goal is to put all the power and flexibility of Blender behind
a general-purpose data visualization system.

Future plans:
	- Linear data plot (cross-section primitive)
	- Pie charts
	- Landscape charts
	- Autoscaling (bar, landscape, linear)
	- Automatic magnitude ordering (pie)
	- Customizing chart parameters
		- Bar
			- Bar spacing
		- Pie
			- Wedge stepping
			- Wedge exploding
	- Arbitrary formula plotting
	- Additional data import formats (XML)
	- Independent values in first column of data
	- Set labels in first row of data
	- Individual data point labels in an adjacent column
	- Toggle horizontal sets from source file
	- Pre-built backdrops/lighting
	- Pre-built materials
	- Updates to Blender 2.5/BPY
"""

import Blender
from Blender import NMesh
import bpy
import re
import csv


class DPDataContainer:
	""" Global data container class. """
	def __init__(self):
		""" Initialize the container. """
		self.series = []
		self.user = { 'type': 1 }


DPDATA = DPDataContainer();


class DataSeries:
	""" Object to manage an entire data series. """
	def __init__(self, series):
		""" Initializes the object and loads the data. """
		# Make sure there is a list of something to import.
		if len(series) > 0:
			# Convert each element into floating numbers (for Blender)
			self.data = map(self.getDatum, series)
			# Shortcut to number of data points
			self.length = len(self.data)
			# Data statistics
			self.min = min(self.data)
			self.max = max(self.data)
			self.avg = sum(self.data) / self.length
		# Empty list, initialize everything to empty/blank.
		else:
			self.data = []
			self.length = 0
			self.min = 0.0
			self.max = 0.0
			self.avg = 0.0
	def getDatum(self, element):
		""" Converts individual datum into numeric types. """
		element = element.strip()
		if len(element) == 0:
			return 0.0
		return float(element)



def loadData(path):
	""" Loads CSV data from a file. """
	global DPDATA

	# Busy cursor.
	Blender.Window.WaitCursor(1)

	# Open the input file for reading (as CSV data).
	file = csv.reader(open(path))

	# Buffer the data from the CSV file.
	data = []
	for row in file:
		data.append(row)

	# Rotate the data using NESTED LIST COMPREHENSION.
	datar = [[row[i] for row in data] for i in range(0,len(data[0]))]

	# Build a list of DataSeries objects.
	DPDATA.series = [DataSeries(col) for col in datar]

	# Data loading feedback.
	print '%i Data series loaded (min/max/avg)...' % (len(DPDATA.series))
	for i in range(0, len(DPDATA.series)):
		s = DPDATA.series[i]
		print '  S%02i: %.2f/%.2f/%.2f' % (i, s.min, s.max, s.avg)

	# Reset cursor to normal.
	Blender.Window.WaitCursor(0)



def drawPlot():
	""" Draws the plot based on user specs. """
	global DPDATA

	if DPDATA.user['type'] == 2:
		DPDATA.user['fake'] = 1
	else:
		for i in range(0, len(DPDATA.series)):
			s = DPDATA.series[i]
			for j in range(0, s.length):
				drawBar(j*1.5, i*1.5, s.data[j])

	# Draw.
	Blender.Redraw()


def drawBar(oX, oY, height):
	""" Draws a single bar in a bar chart. """
	bar = NMesh.GetRaw()
	# Square base
	bar.verts.append(NMesh.Vert(oX, oY+0.0, 0.0))
	bar.verts.append(NMesh.Vert(oX+1.0, oY+0.0, 0.0))
	bar.verts.append(NMesh.Vert(oX+1.0, oY+1.0, 0.0))
	bar.verts.append(NMesh.Vert(oX, oY+1.0, 0.0))
	f = NMesh.Face()
	f.v.append(bar.verts[0])
	f.v.append(bar.verts[1])
	f.v.append(bar.verts[2])
	f.v.append(bar.verts[3])
	bar.faces.append(f)
	# Square top
	bar.verts.append(NMesh.Vert(oX, oY+0.0, height))
	bar.verts.append(NMesh.Vert(oX+1.0, oY+0.0, height))
	bar.verts.append(NMesh.Vert(oX+1.0, oY+1.0, height))
	bar.verts.append(NMesh.Vert(oX, oY+1.0, height))
	f = NMesh.Face()
	f.v.append(bar.verts[4])
	f.v.append(bar.verts[5])
	f.v.append(bar.verts[6])
	f.v.append(bar.verts[7])
	bar.faces.append(f)
	# Walls
	f = NMesh.Face()
	f.v.append(bar.verts[0])
	f.v.append(bar.verts[1])
	f.v.append(bar.verts[5])
	f.v.append(bar.verts[4])
	bar.faces.append(f)
	f = NMesh.Face()
	f.v.append(bar.verts[1])
	f.v.append(bar.verts[2])
	f.v.append(bar.verts[6])
	f.v.append(bar.verts[5])
	bar.faces.append(f)
	f = NMesh.Face()
	f.v.append(bar.verts[2])
	f.v.append(bar.verts[3])
	f.v.append(bar.verts[7])
	f.v.append(bar.verts[6])
	bar.faces.append(f)
	f = NMesh.Face()
	f.v.append(bar.verts[3])
	f.v.append(bar.verts[0])
	f.v.append(bar.verts[4])
	f.v.append(bar.verts[7])
	bar.faces.append(f)
	return NMesh.PutRaw(bar)



def drawGUI():
	""" Draws the GUI to simplify drawing plots. """
	global DPDATA
	Blender.BGL.glClear(Blender.BGL.GL_COLOR_BUFFER_BIT)
	Blender.Draw.PushButton(
		'1. Load CSV',10,10,100,100,20,'Load CSV data from a file.')
	DPDATA.user['typeButton'] = Blender.Draw.Menu(
		'2. Plot Type %t|Bar %x1', 20,10,70,100,20,1,
		'Select the type of plot.')
	Blender.Draw.PushButton(
		'3. Draw',30,10,40,100,20,
		'Draw the mesh depicting the loaded data.')
	Blender.Draw.PushButton(
		'4. Exit',99,10,10,100,20,'Exit the interface.')


def handleKey(event, value):
	""" Handles key press events in this window. """
	if event == Blender.Draw.ESCKEY:
		Blender.Draw.Exit()
	return


def handleButton(event):
	""" Handles button press events in this window. """
	global DPDATA
	if event == 10:
		Blender.Window.FileSelector(loadData, 'Load CSV')
	elif event == 20:
		DPDATA.user['type'] = DPDATA.user['typeButton'].val
	elif event == 30:
		drawPlot()
	elif event == 99:
		Blender.Draw.Exit()
	return


# Render the UI.
Blender.Draw.Register(drawGUI, handleKey, handleButton)
