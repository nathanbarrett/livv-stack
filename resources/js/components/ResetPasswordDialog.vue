<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import isEmail from "validator/es/lib/isEmail";
import axios from "@js/common/axios";

const openDialog = ref<boolean>(false);
const resetPasswordToken = computed<string|null>(() => usePage().props.passwordResetToken as string);
const resetPasswordEmail = computed<string|null>(() => usePage().props.passwordResetEmail as string);
const newPassword = ref<string>("");
const newPasswordConfirmation = ref<string>("");
const updatingPassword = ref<boolean>(false);
const error = ref<string>('');
const passwordReset = ref<boolean>(false);

const formReady = computed<boolean>(() => {
    return resetPasswordToken.value &&
        isEmail(resetPasswordEmail.value) &&
        newPassword.value &&
        newPassword.value.length >= 8 &&
        newPasswordConfirmation.value &&
        newPassword.value === newPasswordConfirmation.value
});
onMounted(() => {
    if (resetPasswordToken.value && isEmail(resetPasswordEmail.value)) {
        openDialog.value = true;
    }
});

async function updatePassword() {
    if (!formReady.value || updatingPassword.value) {
        return;
    }
    updatingPassword.value = true;
    error.value = "";
    passwordReset.value = false;

    try {
        await axios.post("/reset-password", {
            token: resetPasswordToken.value,
            email: resetPasswordEmail.value,
            password: newPassword.value,
        });
        openDialog.value = false;
    } catch (e) {
        error.value = "Something went wrong.";
    } finally {
        updatingPassword.value = false;
    }

    if (error.value) {
        return;
    }

    passwordReset.value = true;
    router.visit("/");
}
</script>

<template>
  <div>
    <v-dialog
      v-model="openDialog"
      :persistent="true"
      width="400"
    >
      <v-card>
        <v-card-title>
          <span class="headline">Reset Password</span>
        </v-card-title>
        <v-card-text>
          <v-form>
            <v-text-field
              v-model="newPassword"
              :rules="[v => !!v || 'Password is required', v => v.length >= 8 || 'Password must be at least 8 characters long']"
              label="New password"
              required
              type="password"
            />
            <v-text-field
              v-model="newPasswordConfirmation"
              :rules="[v => !!v || 'Password confirmation is required', v => v === newPassword || 'Passwords do not match']"
              label="New password confirmation"
              required
              type="password"
            />
          </v-form>
          <v-alert
            v-if="error"
            type="error"
          >
            {{ error }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            :disabled="updatingPassword"
            @click="openDialog = false"
          >
            Cancel
          </v-btn>
          <v-btn
            variant="elevated"
            color="primary"
            :disabled="updatingPassword || !formReady"
            :loading="updatingPassword"
            @click="updatePassword"
          >
            Reset Password
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-snackbar
      v-model="passwordReset"
      color="success"
    >
      Password reset successfully. And you have been logged in.
    </v-snackbar>
  </div>
</template>

<style scoped lang="css">

</style>
