<script setup lang="ts">
import { watch, ref, computed } from "vue";
import axios from "@js/common/axios";
import isEmail from "validator/es/lib/isEmail";

const props = defineProps<{
    open?: boolean
}>();

const dialogOpen = ref<boolean>(false);
const fetching = ref<boolean>(false);
const error = ref<string>("");
const email = ref<string>("");
const password = ref<string>("");
const emailInput = ref<HTMLElement|null>(null);

const formReady = computed<boolean>(() => {
    return isEmail(email.value) &&
        (
            forgotPassword.value ||
            password.value.length > 0
        )
});

// autofocus the email field when the dialog opens
watch(() => props.open, (open) => {
    if (open && !dialogOpen.value) {
        dialogOpen.value = true;
        setTimeout(() => {
            // focus the email field
            emailInput.value?.focus();
        }, 500)
    }
});

const forgotPassword = ref<boolean>(false);
const resetPasswordEmailSent = ref<boolean>(false);
async function sendForgotPasswordEmail(): Promise<void> {
    if (fetching.value) {
        return;
    }
    fetching.value = true;
    resetPasswordEmailSent.value = false;
    try {
        await axios.post("/forgot-password", {
            email: email.value
        });
    } catch (e) {
        error.value = "Something went wrong.";
        fetching.value = false;
        return;
    }

    fetching.value = false;
    resetPasswordEmailSent.value = true;
    dialogOpen.value = false;
}

async function login(): Promise<void> {
    fetching.value = true;
    try {
        await axios.post("/login", {
            email: email.value,
            password: password.value
        });
        window.location.reload();
    } catch (e) {
        error.value = "Invalid email / password combination";
    } finally {
        fetching.value = false;
    }
}
</script>

<template>
  <div>
    <v-dialog
      v-model="dialogOpen"
      activator="#loginButton"
      width="400"
      @close="() => forgotPassword = false"
    >
      <v-card>
        <v-card-title>
          <span class="headline">
            {{ forgotPassword ? "Forgot Password" : "Login" }}
          </span>
        </v-card-title>
        <v-card-text>
          <v-text-field
            ref="emailInput"
            v-model="email"
            autocomplete="username"
            :disabled="fetching"
            label="Email"
            prepend-inner-icon="mdi-account"
            type="email"
            required
          />
          <v-text-field
            v-if="!forgotPassword"
            v-model="password"
            autocomplete="password"
            :disabled="fetching"
            label="Password"
            prepend-inner-icon="mdi-lock"
            type="password"
            required
            @keyup.enter="login"
          />
          <v-alert
            v-if="error"
            type="error"
            class="mt-5"
            dismissible
          >
            {{ error }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-btn
            size="x-small"
            @click="forgotPassword = !forgotPassword"
          >
            {{ forgotPassword ? "Login" : "Forgot Password?" }}
          </v-btn>
          <v-spacer />
          <v-btn
            :disabled="fetching"
            @click="dialogOpen = false"
          >
            Cancel
          </v-btn>
          <v-btn
            v-if="forgotPassword"
            color="primary"
            variant="outlined"
            :disabled="fetching || !formReady"
            :loading="fetching"
            @click="sendForgotPasswordEmail"
          >
            Reset Password
          </v-btn>
          <v-btn
            v-else
            color="primary"
            variant="outlined"
            :disabled="fetching || !formReady"
            :loading="fetching"
            @click="login"
          >
            Login
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-snackbar
      v-model="resetPasswordEmailSent"
      color="success"
      bottom
      :multi-line="true"
    >
      If that email address is associated with an account, <br>
      we've sent you an email with instructions on how to reset your password.
    </v-snackbar>
  </div>
</template>
