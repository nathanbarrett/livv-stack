<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { ServerFlashMessage, ServerFlashMessages, FlashMessageConfig } from "@js/contracts/session-flash-messages";
import { SnackbarLocation } from "@js/common/snackbar";

const DEFAULT_LOCATION: SnackbarLocation = "bottom center";
const DEFAULT_TIMEOUT = 5000;

interface FlashMessages {
    success: FlashMessageConfig;
    warning: FlashMessageConfig;
    error: FlashMessageConfig;
    info: FlashMessageConfig;
    location: SnackbarLocation;
}

const successFallbackColor = "success";
const warningFallbackColor = "warning";
const errorFallbackColor = "error";
const infoFallbackColor = "info";

const showSuccess = ref<boolean>(false);
const showWarning = ref<boolean>(false);
const showError = ref<boolean>(false);
const showInfo = ref<boolean>(false);

const flashData = computed<FlashMessages>(() => {
    const serverFlashData = usePage().props.flash as ServerFlashMessages;

    return {
        success: formatFlashMessage(serverFlashData.success, successFallbackColor, DEFAULT_TIMEOUT),
        warning: formatFlashMessage(serverFlashData.warning, warningFallbackColor, DEFAULT_TIMEOUT),
        error: formatFlashMessage(serverFlashData.error, errorFallbackColor, DEFAULT_TIMEOUT),
        info: formatFlashMessage(serverFlashData.info, infoFallbackColor, DEFAULT_TIMEOUT),
        location: serverFlashData.location || DEFAULT_LOCATION
    }
});

function formatFlashMessage(flashMessage: ServerFlashMessage, fallbackColor: string, timeout: number): FlashMessageConfig {
    if (!flashMessage || typeof flashMessage === "string") {
        return {
            message: typeof flashMessage === 'string' ? flashMessage : '',
            color: fallbackColor,
            timeout
        }
    }

    return {
        color: fallbackColor,
        timeout,
        ...flashMessage
    }
}

onMounted(() => {
    if (flashData.value.success.message) {
        showSuccess.value = true;
    }
    if (flashData.value.error.message) {
        showError.value = true;
    }
    if (flashData.value.info.message) {
        showInfo.value = true;
    }
    if (flashData.value.warning.message) {
        showWarning.value = true;
    }
});
</script>

<template>
  <div>
    <v-snackbar
      v-model="showSuccess"
      :color="flashData.success.color"
      :timeout="flashData.success.timeout"
      :location="flashData.location"
      :multi-line="true"
    >
      {{ flashData.success.message }}
    </v-snackbar>
    <v-snackbar
      v-model="showWarning"
      :color="flashData.warning.color"
      :timeout="flashData.warning.timeout"
      :location="flashData.location"
      :multi-line="true"
    >
      {{ flashData.warning.message }}
    </v-snackbar>
    <v-snackbar
      v-model="showError"
      :color="flashData.error.color"
      :timeout="flashData.error.timeout"
      :location="flashData.location"
      :multi-line="true"
    >
      {{ flashData.error.message }}
    </v-snackbar>
    <v-snackbar
      v-model="showInfo"
      :color="flashData.info.color"
      :timeout="flashData.info.timeout"
      :location="flashData.location"
      :multi-line="true"
    >
      {{ flashData.info.message }}
    </v-snackbar>
  </div>
</template>
