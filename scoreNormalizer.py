
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

		writer.writerow(["Name", "email", "course", "End date", "score"])

		writer.writerows(Processed)


	print("Data exported to "+ courseName +".csv")


def process(courseSelect):
	mydb=connect()
	mycursor=mydb.cursor()

	mycursor.execute("SELECT course_ID, code FROM course WHERE code= %s",[courseSelect])
	course_tuple = mycursor.fetchall()
	#get the 0th index from the 0th tuple
	course = course_tuple[0]
	course_ID = course[0]
	course_code = course[1]
	surveys={}
	mycursor.execute("SELECT DISTINCT eval.survey_id, surveys.expiration_date FROM eval INNER JOIN surveys ON eval.survey_id = surveys.id WHERE course_id= %s",[course_ID])
	myresult = mycursor.fetchall()
	for survey in myresult:
		print(survey)
		surveys[survey[0]] = [survey[1]]
	
	student_info = {}
	mycursor.execute("SELECT DISTINCT teammates.student_ID, students.name, students.email FROM teammates INNER JOIN students ON teammates.student_ID=students.student_ID WHERE course_ID= %s",[course_ID])
	submitters = mycursor.fetchall()
	submitter_IDs =[]
	for submitter in submitters:
		student_info[submitter[0]] = [submitter[1],submitter[2]]
		submitter_IDs.append(submitter[0])
	print(surveys)
	solution= {}
	for id,date in surveys.items():

		results ={}
		for submitter in submitter_IDs:
			denom=0
			mycursor.execute("SELECT DISTINCT students.student_ID, scores.score1,scores.score2, scores.score3, scores.score4, scores.score5, eval.teammate_ID, eval.survey_id FROM students INNER JOIN teammates on students.student_ID =teammates.student_ID INNER JOIN eval on teammates.course_id = eval.course_id AND teammates.student_ID = eval.submitter_ID INNER JOIN scores ON eval.id = scores.eval_key where teammates.course_ID = %s AND students.student_ID = %s AND eval.survey_id =%s ",(course_ID,submitter,id))
			myresult = mycursor.fetchall()
			for x in myresult:
				denom+= x[1] + x[2] + x[3] + x[4] + x[5]
			mycursor.execute("SELECT DISTINCT students.student_ID, scores.score1,scores.score2, scores.score3, scores.score4, scores.score5, eval.teammate_ID, eval.survey_id FROM students INNER JOIN teammates on students.student_ID =teammates.student_ID INNER JOIN eval on teammates.course_id = eval.course_id AND teammates.student_ID = eval.submitter_ID INNER JOIN scores ON eval.id = scores.eval_key where teammates.course_ID = %s AND students.student_ID = %s AND eval.survey_id =%s ",(course_ID,submitter,id))
			myresult = mycursor.fetchall()
			norm={}
			for x in myresult:
				norm[(x[6],x[7])]= (x[1] + x[2] + x[3] + x[4] + x[5])/denom
			for entry in norm.keys():
				if entry in results:
					results[entry].append(norm[entry])
				else:
					results[entry]=[norm[entry]]
		
		for entry in results.keys():
			solution[entry] = []
			print(entry)
			print(student_info)
			solution[entry].append(student_info[entry[0]][0])
			solution[entry].append(student_info[entry[0]][1])
			solution[entry].append(course_code)
			solution[entry].append(date[0].strftime("%m/%d/%Y"))
			solution[entry].append(sum(results[entry])/len(results[entry]))
	createCSV(solution,course_code)

   



if "__name__==__main__":
	inputer= str(sys.argv[1])
	process(inputer)
	
	
