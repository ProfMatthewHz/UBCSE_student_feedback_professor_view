import '../styles/index.css';
import React from "react";

export class SideBar extends React.Component {

  constructor(props) {
    super();
    this.state = {
      course: '<div class="no-courses">No Courses</div>'
    }
  }

  AddCourse() {
    if (this.state.course == '<div class="no-courses">No Courses</div>') {
      this.state.course = ''
    }
    this.setState({
      course: this.state.course + '<a href=\"CSE302\"><div class=\"sidebar-option\">CSE302</div></a>'
    });
  }

  render () {
    return (
      <div class="sidebar">
        <div class="sidebar-content">
          <h1>Courses</h1>
          <div class="sidebar-list" dangerouslySetInnerHTML={{__html: this.state.course}}>
          </div>
          <button onClick={()=> this.AddCourse()}>+ Add Course</button>
        </div>
      </div>
    )
  }
};

export default SideBar;
