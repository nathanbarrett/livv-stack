
import isEmail from "validator/es/lib/isEmail";


export type ValidationResult = string | boolean;
export type ValidationRule = ValidationResult | PromiseLike<ValidationResult> | ((value: FormInput) => ValidationResult) | ((value: FormInput) => PromiseLike<ValidationResult>);
export type ValidationRuleGenerator = (value?: string|number|null|undefined) => ValidationRule;
export type FormRule = ValidationRule | ValidationRuleGenerator;
export type FormInput = string | number | null | undefined;
export type EqualsInput = FormInput | (() => FormInput);

export interface FormRules {
    [key: string]: ValidationRuleGenerator;
}

export const formRules = {
    required: (msg?: string) => {
        return (value: FormInput) => {
            return !!value || msg || 'Required';
        }
    },
    email: (msg?: string) => {
        return (value: FormInput) => {
            return isEmail(value.toString()) || msg || 'Invalid email';
        }
    },
    min: (min: number) => {
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
    max: (max: number) => {
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
    equals: (other: EqualsInput, msg?: string) => {
        return (value: FormInput) => {
            const otherValue = typeof other === 'function' ? other() : other;
            return value === otherValue || msg || `Must be equal to ${otherValue}`
        }
    }
} satisfies FormRules;
