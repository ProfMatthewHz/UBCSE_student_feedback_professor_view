
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


    mycursor.execute("Select Name, email, course , group_number, submitted_scores FROM cse442 WHERE course= %s",[classSelect])

    myresult = mycursor.fetchall()

    print("Data Gathered")

    rawArr=[]

    for x in myresult:
        rawStr=str(x)
        processedStr=rawStr.split(", ")
        rawArr.append(processedStr)

    for elems in rawArr:
        elems[0]=elems[0][2:]
        elems[0]=elems[0][:-1]
        elems[1]=elems[1][1:]
        elems[1]=elems[1][:-1]
        elems[2]=elems[2][1:]
        elems[2]=elems[2][:-1]
        elems[4]=elems[4][1:]
        elems[4]=elems[4][:-2]
        #for b in elems:
            #print(b)
        #print(" ")
    print("Data Processed")
    ProcessedFull={}
    NormScores={}
    for elems2 in rawArr:
        Processed=[]
        temper=[]
        temper2=[]
        Processed.append(elems2[0])
        Processed.append(elems2[1])
        Processed.append(elems2[2])
        Processed.append(elems2[3])
        Processed.append(temper)
        Processed.append(temper2)
        ProcessedFull[elems2[1]]=Processed


        SubmitScore=elems2[4].split(":")

        emails=[]
        numerators=[]
        for x in SubmitScore:
            temp=x.split(",")
            emails.append(temp[0])
            del(temp[0])
            convertToInt=list(map(int,temp))
            numerators.append(sum(convertToInt))

        denominator=sum(numerators)

        if denominator > 0:

            counter=0
            for x in numerators:
                temp=x/denominator
                if emails[counter] in NormScores:
                    NormScores[emails[counter]].append(temp)
                else:
                    NormScores[emails[counter]]=[]
                    NormScores[emails[counter]].append(temp)
                counter+=1

    for key in NormScores.keys():
        numStudents=len(NormScores[key])
        temp=0
        for x in NormScores[key]:
            temp+=x
        ProcessedFull[key][4]=temp/numStudents
        ProcessedFull[key][5]=temp


    createCSV(ProcessedFull)







if "__name__==__main__":
    inputer= str(sys.argv[1])
    process(inputer)
