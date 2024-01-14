<script setup lang="ts">

</script>

<template>
  <v-row>
    <v-col
      cols="12"
      class="d-flex justify-center"
    >
      <h2>Available Components</h2>
    </v-col>
    <v-col cols="12">
      <v-row justify="center">
        <v-col
          cols="12"
          md="10"
        >
          <v-card>
            <v-card-title>
              <h3>Buttons</h3>
            </v-card-title>
            <v-card-text class="d-flex justify-lg-space-between flex-wrap">
              <v-btn
                variant="flat"
                color="primary"
                class="mr-5 mt-10"
              >
                Primary
              </v-btn>
              <v-btn
                variant="flat"
                color="secondary"
                class="mr-5 mt-10"
              >
                Secondary
              </v-btn>
              <v-btn
                variant="flat"
                color="accent"
                class="mr-5 mt-10"
              >
                Accent
              </v-btn>
              <v-btn
                variant="flat"
                color="error"
                class="mr-5 mt-10"
              >
                Error
              </v-btn>
              <v-btn
                variant="flat"
                color="warning"
                class="mr-5 mt-10"
              >
                Warning
              </v-btn>
              <v-btn
                variant="flat"
                color="info"
                class="mr-5 mt-10"
              >
                Info
              </v-btn>
              <v-btn
                variant="flat"
                color="success"
                class="mr-5 mt-10"
              >
                Success
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col
          cols="12"
          md="10"
        >
          <v-card>
            <v-card-title>
              <h3>Alerts</h3>
            </v-card-title>
            <v-card-text>
              <v-alert
                title="Success!"
                type="success"
              >
                This is a success alert.
              </v-alert>
              <v-alert
                title="Info!"
                type="info"
                class="mt-10"
              >
                This is an info alert.
              </v-alert>
              <v-alert
                title="Warning!"
                type="warning"
                class="mt-10"
              >
                This is a warning alert.
              </v-alert>
              <v-alert
                title="Error!"
                type="error"
                class="mt-10"
              >
                This is an error alert.
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-col>
  </v-row>
</template>

<style scoped lang="css">

</style>
