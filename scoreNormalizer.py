
import sys
import mysql.connector
import csv

#connect to sql database, handle errors.
def connect():
	print("Connecting")
	try:

		conn = mysql.connector.connect(
			host="tethys.cse.buffalo.edu",
			user="jeh24",
			passwd="50172309",
			database="cse442_542_2019_summer_teame_db"
		)
		if conn.is_connected():
			print("Connection to database established.")
		else:
			print("connection failed.")
		return conn
	except Error as e:
		print(e)
	return conn


def createCSV(inputDict,courseName):
	Processed=[]
	for valls in inputDict.values():
		Processed.append(valls)
	with open(courseName + ".csv", "w") as f:
		writer = csv.writer(f)

		writer.writerow(["Name", "email", "course", "Group Number", "Points Percentage", "score"])

		writer.writerows(Processed)


	print("Data exported to "+ courseName +".csv")


def process(classSelect):
	mydb=connect()
	mycursor=mydb.cursor()

	mycursor.execute("SELECT course_ID, code FROM course WHERE code= %s",[classSelect])
	ClassTuple = mycursor.fetchall()
	#get the 0th index from the 0th tuple
	Class=ClassTuple[0]
	Class_ID=Class[0]
	Class_code= Class[1]
	studentInfo = {}
	mycursor.execute("SELECT DISTINCT teammates.student_ID, students.name, students.email FROM teammates INNER JOIN students ON teammates.student_ID=students.student_ID WHERE course_ID= %s",[Class_ID])
	Submitters = mycursor.fetchall()
	Submitter_IDs =[]
	for submitter in Submitters:
		studentInfo[submitter[0]] = [submitter[1],submitter[2]]
		Submitter_IDs.append(submitter[0])
	
	results ={}
	for submitter in Submitter_IDs:
		denom=0   
		mycursor.execute("SELECT DISTINCT students.student_ID, scores.score1,scores.score2, scores.score3, scores.score4, scores.score5, eval.teammate_ID FROM students INNER JOIN teammates on students.student_ID =teammates.student_ID and teammates.course_ID = %s INNER JOIN eval on teammates.teammate_key = eval.teammate_key AND teammates.student_ID = eval.submitter_ID INNER JOIN scores ON eval.id = scores.eval_key where students.student_ID = %s",[Class_ID,submitter])
		myresult = mycursor.fetchall()
		for x in myresult:
			denom+= x[1] + x[2] + x[3] + x[4] + x[5]
		mycursor.execute("SELECT DISTINCT students.student_ID, scores.score1,scores.score2, scores.score3, scores.score4, scores.score5, eval.teammate_ID FROM students INNER JOIN teammates on students.student_ID =teammates.student_ID and teammates.course_ID = %s INNER JOIN eval on teammates.teammate_key = eval.teammate_key AND teammates.student_ID = eval.submitter_ID INNER JOIN scores ON eval.id = scores.eval_key where students.student_ID = %s",[Class_ID,submitter])
		myresult = mycursor.fetchall()
		norm={}
		for x in myresult:
			norm[x[6]]= (x[1] + x[2] + x[3] + x[4] + x[5])/denom	
		for entry in norm.keys():
			if entry in results:
				results[entry].append(norm[entry])
			else:
				results[entry]=[norm[entry]]
	solution= {}
	for entry in results.keys():
		solution[entry] = []
		solution[entry].append(studentInfo[entry][0])
		solution[entry].append(studentInfo[entry][1])
		solution[entry].append(Class_code)
		solution[entry].append(sum(results[entry])/len(results[entry]))
	createCSV(solution,Class_code)

   



if "__name__==__main__":
	inputer= str(sys.argv[1])
	process(inputer)
	
	
