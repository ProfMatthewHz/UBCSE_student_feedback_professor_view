import StudentSideBar from "../Components/studentSidebar";
import "../styles/home.css";
import "../styles/rubricCourse.css";
import "../styles/sidebar.css";
import SurveyListing from "../Components/SurveyListing";


/**
 * This will be rendered for students
 */
const StudentHome = () => {

  /**
   * The Home component renders a SideBar component and a list of Course components.
   */
  return (
    <>
      <StudentSideBar />
      <SurveyListing return_to="/" />
    </>
  );
};

export default StudentHome;