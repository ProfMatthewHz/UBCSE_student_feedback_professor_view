
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


def createCSV(inputDict):
	Processed=[]
	for valls in inputDict.values():
		Processed.append(valls)
	with open("scores.csv", "w") as f:
		writer = csv.writer(f)

		writer.writerow(["Name", "email", "Course", "Group Number", "Points Percentage", "Score"])

		writer.writerows(Processed)


	print("Data exported to scores.csv")


def process(classSelect):
	mydb=connect()
	mycursor=mydb.cursor()

	mycursor.execute("Select Course_ID FROM course WHERE code= %s",[classSelect])
	Class_ID = mycursor.fetchall()
	print(Class_ID)
	#get the 0th index from the 0th tuple
	Class_ID=Class_ID[0]
	Class_ID=Class_ID[0]
	print(Class_ID)
	mycursor.execute("SELECT DISTINCT Student_ID FROM Teammates WHERE Course_ID= %s",[Class_ID])
	Submitters = mycursor.fetchall()
	Submitter_IDs =[]
	for submitter in Submitters:
		Submitter_IDs.append(submitter[0])
	print(Submitter_IDs)
	
	results ={}
	for submitter in Submitter_IDs:
		denom=0   
		mycursor.execute("SELECT DISTINCT Students.Student_ID, Scores.Score1,Scores.Score2, Scores.Score3, Scores.Score4, Scores.Score5, Eval.Teammate_ID FROM Students INNER JOIN Teammates on Students.Student_ID =Teammates.Student_ID and Teammates.Course_ID = %s INNER JOIN Eval on Teammates.Teammate_key = Eval.Teammate_key AND Teammates.Student_ID = Eval.Submitter_ID INNER JOIN Scores ON Eval.id = Scores.Eval_key where Students.Student_ID = %s",[Class_ID,submitter])
		myresult = mycursor.fetchall()
		for x in myresult:
			print(x)
			denom+= x[1] + x[2] + x[3] + x[4] + x[5]
		mycursor.execute("SELECT DISTINCT Students.Student_ID, Scores.Score1,Scores.Score2, Scores.Score3, Scores.Score4, Scores.Score5, Eval.Teammate_ID FROM Students INNER JOIN Teammates on Students.Student_ID =Teammates.Student_ID and Teammates.Course_ID = %s INNER JOIN Eval on Teammates.Teammate_key = Eval.Teammate_key AND Teammates.Student_ID = Eval.Submitter_ID INNER JOIN Scores ON Eval.id = Scores.Eval_key where Students.Student_ID = %s",[Class_ID,submitter])
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
		solution[entry] = sum(results[entry])/len(results[entry])
	print(solution)
	print(sum(solution.values()))
	print("Data Gathered")

   



if "__name__==__main__":
	inputer= str(sys.argv[1])
	process(inputer)
	
	
