import {useId} from "react";
import '../styles/dropdown.css';

const Dropdown = ({value, onChange, options}) => {
    const id = useId()
    
    return (
        <div className="dropdown">
            <select id={id} value={value} onChange={event => onChange(event.target.value)}>
                {options.map(option => (
                    <option key={option.value} value={option.value} disabled={options[0] === option}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    )

}

export default Dropdown;
