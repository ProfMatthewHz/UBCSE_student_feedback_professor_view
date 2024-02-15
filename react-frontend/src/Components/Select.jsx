import {useEffect, useRef, useState} from "react";
import styles from "../styles/select.module.css";

export function Select({multiple, value, onChange, options}) {
    const [isOpen, setIsOpen] = useState(false);
    const [highlightedIndex, setHighlightedIndex] = useState(0);
    const containerRef = useRef(null);

    /**
     * Closes the dropdown menu when the user clicks outside the container.
     */
    function clearOptions() {
        multiple ? onChange([]) : onChange(undefined);
    }

    /**
     * Selects an option from the dropdown menu.
     * @param option
     */
    function selectOption(option) {
        if (multiple) {
            const isOptionIncluded = value.some(
                (val) => val.label === option.label && val.value === option.value
            );
            if (isOptionIncluded) {
                // Remove the option object from the value array
                const newValue = value.filter(
                    (val) => val.label !== option.label || val.value !== option.value
                );
                onChange(newValue);
            } else {
                onChange([...value, option]);
            }
        } else {
            if (option !== value) onChange(option);
        }
    }

    /**
     * Determines if an option is selected.
     * @param option
     * @returns {*|boolean}
     */
    function isOptionSelected(option) {
        if (multiple) {
            const isOptionIncluded = value.some(
                (val) => val.label === option.label && val.value === option.value
            );
            return isOptionIncluded;
        } else {
            return option === value;
        }
    }

    /**
     * Closes the dropdown menu when the user clicks outside the container.
     */
    useEffect(() => {
        if (isOpen) setHighlightedIndex(0);
    }, [isOpen]);

    // Close the dropdown menu when the user clicks outside the container
    return (
        <div
            ref={containerRef}
            onBlur={() => setIsOpen(false)}
            onClick={() => setIsOpen((prev) => !prev)}
            tabIndex={0}
            className={styles.container}
        >
      <span className={styles.value}>
        {multiple ? (
            value.length > 0 ? (
                value.map((v) => (
                    <button
                        key={v.value}
                        onClick={(e) => {
                            e.stopPropagation();
                            selectOption(v);
                        }}
                        className={styles["option-badge"]}
                    >
                        {v.label}
                        <span className={styles["remove-btn"]}>&times;</span>
                    </button>
                ))
            ) : (
                <span>Select an option to add additional instructor(s)</span>
            )
        ) : (
            value?.label
        )}
      </span>
            <button
                onClick={(e) => {
                    e.stopPropagation();
                    clearOptions();
                }}
                className={styles["clear-btn"]}
            >
                &times;
            </button>
            <div className={styles.divider}></div>
            <div className={styles.caret}></div>
            <ul className={`${styles.options} ${isOpen ? styles.show : ""}`}>
                {options.map((option, index) => (
                    <li
                        onClick={(e) => {
                            e.stopPropagation();
                            selectOption(option);
                            setIsOpen(false);
                        }}
                        onMouseEnter={() => setHighlightedIndex(index)}
                        key={option.value}
                        className={`${styles.option} ${
                            isOptionSelected(option) ? styles.selected : ""
                        } ${index === highlightedIndex ? styles.highlighted : ""}`}
                    >
                        {option.label}
                    </li>
                ))}
            </ul>
        </div>
    );
}
