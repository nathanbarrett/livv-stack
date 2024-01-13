
import isEmail from "validator/es/lib/isEmail";


export type ValidationResult = string | boolean;
export type ValidationRule = ValidationResult | PromiseLike<ValidationResult> | ((value: any) => ValidationResult) | ((value: any) => PromiseLike<ValidationResult>);
export type ValidationRuleGenerator = (value?: any) => ValidationRule;
export type FormRule = ValidationRule | ValidationRuleGenerator;
export interface FormRules {
    [key: string]: ValidationRuleGenerator;
}

export const formRules = {
    required: (msg?: string) => {
        return (value: any) => {
            return !!value || msg || 'Required';
        }
    },
    email: (msg?: string) => {
        return (value: any) => {
            return isEmail(value) || msg || 'Invalid email';
        }
    },
    min: (min: number) => {
        return (value: any) => {
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
        return (value: any) => {
            if (typeof value === 'string') {
                return value.length <= max || `Must be at most ${max} characters`
            }
            if (typeof value === 'number') {
                return value <= max || `Must be at most ${max}`
            }
            return true;
        }
    },
    equals: (other: any, msg?: string) => {
        return (value: any) => {
            const otherValue = typeof other === 'function' ? other() : other;
            return value === otherValue || msg || `Must be equal to ${otherValue}`
        }
    }
} satisfies FormRules;
