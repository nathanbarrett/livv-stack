import isEmail from "validator/es/lib/isEmail";

export type ValidationResult = string | boolean;
export type FormInput = string | number | null | undefined;
export type ValidationRule = (value: FormInput) => ValidationResult;
export type EqualsInput = FormInput | (() => FormInput);

export const formRules = {
    required: (msg?: string): ValidationRule => {
        return (value: FormInput) => {
            return !!value || msg || 'Required';
        }
    },
    email: (msg?: string): ValidationRule => {
        return (value: FormInput) => {
            if (value === null || value === undefined) {
                return msg || 'Invalid email';
            }
            return isEmail(value.toString()) || msg || 'Invalid email';
        }
    },
    min: (min: number): ValidationRule => {
        return (value: FormInput) => {
            if (typeof value === 'string') {
                return value.length >= min || `Must be at least ${min} characters`
            }
            if (typeof value === 'number') {
                return value >= min || `Must be at least ${min}`
            }
            return true;
        }
    },
    max: (max: number): ValidationRule => {
        return (value: FormInput) => {
            if (typeof value === 'string') {
                return value.length <= max || `Must be at most ${max} characters`
            }
            if (typeof value === 'number') {
                return value <= max || `Must be at most ${max}`
            }
            return true;
        }
    },
    equals: (other: EqualsInput, msg?: string): ValidationRule => {
        return (value: FormInput) => {
            const otherValue = typeof other === 'function' ? other() : other;
            return value === otherValue || msg || `Must be equal to ${otherValue}`
        }
    }
};
