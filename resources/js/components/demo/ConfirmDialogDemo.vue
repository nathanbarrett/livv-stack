<script setup lang="ts">
import {computed, ref} from 'vue';
// Use this 👇 function anytime you need a confirmation dialog
import {confirmDialog} from "@js/common/confirm";

const message = ref<string>('Are you sure you want to do this?');
const title = ref<string>('Confirm Action');
const cancelButtonText = ref<string>('Hell No');
const cancelButtonColor = ref<string>('default');
const confirmButtonColor = ref<string>('info');
const confirmButtonText = ref<string>('Affirmative');
const confirmReady = computed<boolean>(() => !!message.value &&
    !!title.value &&
    !!cancelButtonText.value &&
    !!confirmButtonText.value &&
    !!cancelButtonColor.value &&
    !!confirmButtonColor.value
);
const answer = ref<boolean|null>(null);

const confirm = async () => {
    if (!confirmReady.value) {
        return;
    }
    answer.value = null;
    answer.value = await confirmDialog({
        title: title.value,
        message: message.value,
        cancelButtonText: cancelButtonText.value,
        cancelButtonColor: cancelButtonColor.value,
        confirmButtonText: confirmButtonText.value,
        confirmButtonColor: confirmButtonColor.value,
    });
};
</script>

<template>
    <v-card title="Global Confirm Dialog">
        <v-card-subtitle>
            Quickly get user confirmation with one imported function. <br />
            See ConfirmDialogDemo.vue for the example code.
        </v-card-subtitle>
        <v-card-text>
            <v-row>
                <v-col cols="12">
                    <v-text-field
                        v-model="title"
                        label="Dialog Title"
                        outlined />
                    <v-text-field
                        v-model="message"
                        label="Dialog Message"
                        outlined />
                    <v-text-field
                        v-model="confirmButtonText"
                        label="Confirm Button Text"
                        outlined />
                    <v-text-field
                        v-model="confirmButtonColor"
                        label="Confirm Button Color"
                        hint="Any valid CSS hex or rgb color OR a Vuetify color name"
                        persistent-hint
                        outlined />
                    <v-text-field
                        class="mt-5"
                        v-model="cancelButtonText"
                        label="Cancel Button Text"
                        outlined />
                    <v-text-field
                        v-model="cancelButtonColor"
                        label="Cancel Button Color"
                        hint="Any valid CSS hex or rgb color OR a Vuetify color name"
                        persistent-hint
                        outlined />
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <div v-if="answer" class="text-green">
                        Confirmed!
                    </div>
                    <div v-else-if="answer === false" class="text-red">
                        Cancelled!
                    </div>
                    <div v-else class="text-grey">
                        User has not confirmed or denied the action.
                    </div>
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn
                @click="confirm"
                :disabled="!confirmReady"
                color="primary">
                Show Confirm Dialog
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
