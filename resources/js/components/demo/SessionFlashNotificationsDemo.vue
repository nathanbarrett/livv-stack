<script setup lang="ts">
import { ref } from "vue";

const types = ref(["success", "warning", "error", "info"]);
const type = ref("success");

function goToRedirect() {
    window.location.href = `/demo/redirect-with-flash-info?type=${type.value}`;
}
</script>

<template>
    <v-card title="Page Load Notifications">
        <v-card-subtitle>
            Need a fancy way to show notifications from the server? <br />
            Session flash messages to the recue! <br />
            See DemoController.php for the example code.
        </v-card-subtitle>
        <v-card-text>
            <v-select
                v-model="type"
                :items="types"
                label="Notification Type"
                outlined />
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn
                @click="goToRedirect"
                :color="type"
            >
                Redirect with Flash Info
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
