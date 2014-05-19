/****************************************************************************
	Multithreaded Temperature Control System

	Steve Larsen and Zac Hester

	March 3rd, 2006

	For: Dr. Batchelder, CENG 447: Embedded Systems, Lab 4
****************************************************************************/

/*
 * Basic Includes
 */ 
#include "includes.h"
#include "consol.h"
#include "ad.h"


/*  Size of each task's stack (# of words) */
#define  TASK_STK_SIZE      64

/*  Number of concurrent tasks */
#define  NO_TASKS           3

/* Pin configuration: heater circuit assignment */
#define HTR_0 0x02000000
#define HTR_1 0x04000000
#define HTR_2 0x40000000

/*
 * Task variable data structure
 */
typedef struct {
	U32 sensor_num;
	U32 heater_bit;
	U8 temperature;
	U16 period;
	U8 label;
} controller_data;
controller_data system_data[3];

/*
 * Temperature conversion lookup table
 * The index is the 8-bit value of the AD converter.
 * The element value is the temperature in degrees celsius.
 *  (See temperature.pl for details.)
 */
const S16 temp_vector[256] = {
	176,164,147,138,131,126,122,118,115,112,109,107,105,103,101,100,98,
	97,95,94,93,91,90,89,88,87,86,85,84,83,83,82,81,80,79,79,78,77,76,76,
	75,75,74,73,73,72,71,71,70,70,69,69,68,68,67,67,66,66,65,65,64,64,63,
	63,63,62,62,61,61,60,60,60,59,59,58,58,58,57,57,57,56,56,55,55,55,54,
	54,54,53,53,53,52,52,52,51,51,51,50,50,50,49,49,49,48,48,48,48,47,47,
	47,46,46,46,45,45,45,45,44,44,44,43,43,43,43,42,42,42,42,41,41,41,40,
	40,40,40,39,39,39,39,38,38,38,38,37,37,37,37,36,36,36,35,35,35,35,34,
	34,34,34,33,33,33,33,32,32,32,32,32,31,31,31,31,30,30,30,30,29,29,29,
	29,28,28,28,28,27,27,27,27,26,26,26,26,25,25,25,25,24,24,24,24,24,23,
	23,23,23,22,22,22,22,21,21,21,21,20,20,20,20,19,19,19,19,18,18,18,18,
	17,17,17,17,16,16,16,16,15,15,15,15,14,14,14,14,13,13,13,13,12,12,12,
	12,11,11,11,11,10,10,10,9
};

/* Concurrent task stacks */
OS_STK TaskStk[NO_TASKS][TASK_STK_SIZE];

/* Startup task stack */
OS_STK TaskStartStk[TASK_STK_SIZE];

/* Task semaphore for reading the AD converter */
OS_EVENT *TempSem;


/*
 * Function prototypes
 */
void TempControl(void *data);
void TaskStart(void *data);
void APP_vMain(void);


/*=========================================================================*/



/**
 * TempControl
 * Operate a concurrent sense and control task.
 *
 * @param data A pointer to task-specific data
 */
void TempControl(void *data) {

	/* The task-specific data */
	controller_data* sys = (controller_data*) data;

	/* Error code from semaphore */
	U8 err;

	/* Converted AD value */
	U32 adval;

	/* An index variable to "protect" the temperature lookup table */
	U8 temp_index;

	/* The converted temperature */
	U32 temp;

	/* Sets this task's heater state (for output purposes) */
	U8 htr_on = 1;

	/* Enter control loop */
	while(1) {

		/* Acquire semaphore to read temperature sensor */
		OSSemPend(TempSem, 0, &err);

		/* Sense temperature. */
		adval = ConvertAD0(sys->sensor_num);

		/* Shift to 8-bit value */
		adval >>= 2;

		/* Mask and convert to a "safe" index value */
		temp_index = adval & 0x000000FF;

		/* Convert the AD value to degrees celsius */
		temp = temp_vector[temp_index];

		/* Test current temperature against desired temperature. */
		if(temp < sys->temperature) {

			/* Turn on heater. */
			rIOSET0 = sys->heater_bit;
			htr_on = 1;
		}
		else {

			/* Turn off heater. */
			rIOCLR0 = sys->heater_bit;
			htr_on = 0;
		}

		/* Send diagnostic output to the console. */
		CONSOL_SendCh('\n');
		/* Task label (number 0 to 2) */
		CONSOL_PrintNum(10, 2, False, ' ', sys->label);
		CONSOL_SendString(": ");
		/* Thermister temperature (degrees celsius) */
		CONSOL_PrintNum(10, 3, False, ' ', temp);
		CONSOL_SendString("/");
		/* Desired temperature (degrees celsius) */
		CONSOL_PrintNum(10, 3, False, ' ', sys->temperature);
		/* Heater control state */
		if(htr_on) {
			CONSOL_SendString(" Heater ON");
		}
		else {
			CONSOL_SendString(" Heater OFF");
		}

		/* Release semaphore */
		OSSemPost(TempSem);

		/* Wait one second plus the task period */
		OSTimeDlyHMSM(0, 0, 1, sys->period);
	}
}



/**
 * TaskStart
 * Initialize and run the application-specific control tasks.
 *
 * @param data A pointer to task-specific data
 */
void TaskStart(void *data) {
	U8 i = 0;
	char key;

	/* Prevent compiler warning. */
	data = data;

	/* OSStatInit(); /* Initialize uC/OS-II's statistics. */

	CONSOL_SendString("Temperature Lab\n");

	/* Set I/O direction for heater driver circuits */
	rIODIR0 |= HTR_0 | HTR_1 | HTR_2;

	/* Assign task-specific information. */
	/*  (Heater assignment based on changes in physical cicuit.) */		 		
	system_data[0].sensor_num = 1;
	system_data[0].heater_bit = HTR_1;
	system_data[0].temperature = 20;
	system_data[0].period = 159;
	system_data[0].label = 0;
	
	system_data[1].sensor_num = 2;
	system_data[1].heater_bit = HTR_2;
	system_data[1].temperature = 25;
	system_data[1].period = 373;
	system_data[1].label = 1;
	
	system_data[2].sensor_num = 4;
	system_data[2].heater_bit = HTR_0;
	system_data[2].temperature = 40;
	system_data[2].period = 257;
	system_data[2].label = 2;

	/* Initialize A/D converter. */
	InitAD0();

	/* Concurrent task startup loop */
	for (i = 0; i < NO_TASKS; i++) {

		/* Start a control task using its own data structure. */
		OSTaskCreate(
			/* Task to run */
			TempControl,
			/* Data for Task */
			(void *)&system_data[i],
			/* Task stack */
			(void *)&TaskStk[i][TASK_STK_SIZE-1],
			/* Task priority */
			i+1
		);
	}

	/* Console polling loop */
	while(1) {

		OSCtxSwCtr = 0;

		/* See if key has been pressed */
		if(CONSOL_GetChar(&key) == True) {

			/* Yes, see if it's the ESCAPE key */
			if(key == 0x1B) {

				/* Stay here for ever */
				while(1);
			}
		}

		/* Wait one minute */
		OSTimeDlyHMSM(0, 1, 0, 0);
	}
}



/**
 * APP_vMain
 * Primary startup code for the OS
 */
void APP_vMain(void) {

	/* Initialize uC/OS-II */
	OSInit();

	/* Create process synchronization semaphore. */
	TempSem = OSSemCreate(1);

	/* Run the startup task */
	OSTaskCreate(
		TaskStart,
		(void *)0,
		(void *)&TaskStartStk[TASK_STK_SIZE-1],
		0
	);

	/* os_cfg.h */
	FRMWRK_vStartTicker(OS_TICKS_PER_SEC);

	/* Start multitasking */
	OSStart();
}
