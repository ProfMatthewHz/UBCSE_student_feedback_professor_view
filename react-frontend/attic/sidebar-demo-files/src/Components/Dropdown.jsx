import {useId} from "react";

const Dropdown = ({value, onChange, options}) => {
    const id = useId()

    return (
        <div>
            <select id={id} value={value} onChange={event => onChange(event.target.value)}>
                {options.map(option => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    )

}

export default Dropdown;
