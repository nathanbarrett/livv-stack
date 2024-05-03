<script setup lang="ts">
import { ref } from 'vue';
// Just pull these 👇 in anywhere you need them
import { success, info, error, warning, SnackbarLocation, GlobalSnackbarOptions } from "@js/common/snackbar";

const message = ref<string>('Hey there! Check out this notification!');
type MessageType = 'success' | 'info' | 'error' | 'warning';
const messageType = ref<MessageType>('success');
const messageTypes: MessageType[] = ['success', 'info', 'error', 'warning'];
const location = ref<SnackbarLocation>('bottom center');
const locations: SnackbarLocation[] = ['top left', 'top center', 'top right', 'bottom left', 'bottom center', 'bottom right'];

const showSnackbar = () => {
    if (!message.value) {
        return;
    }

    // optional set of options
    const options: GlobalSnackbarOptions = {
        location: location.value,
    }

    switch (messageType.value) {
        case 'success':
            success(message.value, options);
            break;
        case 'info':
            info(message.value, options);
            break;
        case 'error':
            error(message.value, options);
            break;
        case 'warning':
            warning(message.value, options);
            break;
    }
};
</script>

<template>
    <v-card title="Global Snackbar Notifications">
        <v-card-subtitle>
            Global notifications are a breeze. <br />
            View SnackbarDemo.vue to see how it works
        </v-card-subtitle>
        <v-card-text>
            <v-row>
                <v-col cols="12">
                    <v-text-field
                        v-model="message"
                        label="Notification Message"
                        outlined />
                    <v-select
                        v-model="messageType"
                        :items="messageTypes"
                        label="Notification Type"
                        outlined />
                    <v-select
                        v-model="location"
                        :items="locations"
                        label="Notification Location"
                        outlined />
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn
                @click="showSnackbar"
                color="primary"
            >
                Show Notification
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
