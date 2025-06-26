import {cleanup, act, render, screen} from '@testing-library/react';
import '@testing-library/jest-dom'
import Course from "../Components/Course";

afterEach(cleanup);

it("No surveys in history", async () => {
  jest.spyOn(global, "fetch")
    .mockImplementationOnce(() =>    
    Promise.resolve({      
      json: () => Promise.resolve({active: [], expired: [], upcoming: []})
    })).mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve({rubrics: []})
    }));
  // Use the asynchronous version of act to apply resolved promises
  await act(async () => {
    // render the Course component
    const view = render(<Course course={{"name": "Bob", "code": "CSE421", "id" : 123}} page="history"/>);
  });
  expect(screen.getByText("CSE421: Bob")).toBeInTheDocument();
  expect(screen.getByText("No Surveys Created")).toBeInTheDocument();
});

it("No surveys at home", async () => {
  jest.spyOn(global, "fetch")
    .mockImplementationOnce(() =>    
    Promise.resolve({      
      json: () => Promise.resolve({active: [], expired: [], upcoming: []})
    })).mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve({rubrics: []})
    }));
  // Use the asynchronous version of act to apply resolved promises
  await act(async () => {
    // render the Course component
    const view = render(<Course course={{"name": "Bob", "code": "CSE421", "id" : 123}} page="home"/>);
  });
  expect(screen.getByText("CSE421: Bob")).toBeInTheDocument();
  expect(screen.getByText("No Surveys Yet")).toBeInTheDocument();
});

it("One active survey at home", async () => {
  jest.spyOn(global, "fetch")
    .mockImplementationOnce(() =>    
    Promise.resolve({      
      json: () => Promise.resolve({active: [], expired: [], upcoming: []})
    })).mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve({rubrics: []})
    }));
  // Use the asynchronous version of act to apply resolved promises
  await act(async () => {
    // render the Course component
    const view = render(<Course course={{"name": "Bob", "code": "CSE421", "id" : 123}} page="home"/>);
  });
  expect(screen.getByText("CSE421: Bob")).toBeInTheDocument();
  expect(screen.getByText("No Surveys Yet")).toBeInTheDocument();
});
