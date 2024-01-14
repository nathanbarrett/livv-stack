<script setup lang="ts">
import Layout from "@js/Pages/Layout.vue";
import axios from "@js/common/axios";
import { ref } from "vue";
import {ValidationRule, formRules } from "@js/common/form-rules";
import { User } from "@js/contracts/models";
import {AxiosError, AxiosResponse} from "axios";

interface RegisterRequestData {
  name: string;
  email: string;
  password: string;
}
interface RegisterResponseData {
    user: User;
}

const registering = ref<boolean>(false);
const formValid = ref<boolean>(false);

const name = ref<string>("");
const nameRules = ref<ValidationRule[]>([formRules.required()]);

const email = ref<string>("");
const emailRules = ref<ValidationRule[]>([formRules.required(), formRules.email()]);

const password = ref<string>("");
const passwordRules = ref<ValidationRule[]>([formRules.required(), formRules.min(8)]);

const passwordConfirmation = ref<string>("");
const passwordConfirmationRules = ref<ValidationRule[]>([
  formRules.required(),
  formRules.equals(() => password.value, "Password confirmation must match password")
]);

const registrationError = ref<string>("");

async function register(): Promise<void> {
  if (!formValid.value || registering.value) {
    return;
  }
  registering.value = true;
    registrationError.value = "";
  try {
      await axios.post<unknown, AxiosResponse<RegisterResponseData>, RegisterRequestData>("/register", {
          name: name.value,
          email: email.value,
          password: password.value,
      });
      window.location.href = "/";
  } catch (e) {
      if (e instanceof AxiosError) {
          registrationError.value = e.response?.data?.message || "An error occurred";
      }
  } finally {
      registering.value = false;
  }
}
</script>

<template>
  <Layout>
    <v-container>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          md="8"
          lg="6"
          xl="4"
        >
          <v-form
            v-model="formValid"
            validate-on="blur"
            @submit.prevent="register"
          >
            <v-card class="mt-10">
              <v-card-title>
                <span class="headline">
                  Register for an account
                </span>
              </v-card-title>
              <v-card-text>
                <v-text-field
                  v-model="name"
                  required
                  :rules="nameRules"
                  :disabled="registering"
                  label="Name"
                  prepend-inner-icon="mdi-account"
                  type="text"
                  autocomplete="name"
                  class="mt-3"
                />
                <v-text-field
                  v-model="email"
                  required
                  :rules="emailRules"
                  :disabled="registering"
                  label="Email"
                  prepend-inner-icon="mdi-email"
                  type="email"
                  autocomplete="username"
                  class="mt-3"
                />
                <v-text-field
                  v-model="password"
                  required
                  :disabled="registering"
                  :rules="passwordRules"
                  label="Password"
                  prepend-inner-icon="mdi-lock"
                  type="password"
                  autocomplete="new-password"
                  class="mt-3"
                />
                <v-text-field
                  v-model="passwordConfirmation"
                  :disabled="registering"
                  label="Confirm Password"
                  prepend-inner-icon="mdi-lock"
                  type="password"
                  autocomplete="new-password"
                  :rules="passwordConfirmationRules"
                  class="mt-3"
                />
                <v-alert
                  v-if="registrationError"
                  type="error"
                  closeable
                  class="mt-3"
                >
                  {{ registrationError }}
                </v-alert>
              </v-card-text>
              <v-card-actions>
                <v-spacer />
                <v-btn
                  variant="flat"
                  :disabled="!formValid || registering"
                  color="primary"
                  :loading="registering"
                  type="submit"
                >
                  Register
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </Layout>
</template>
