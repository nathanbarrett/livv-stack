<script setup lang="ts">
import AppLayout from '@js/Pages/AppLayout.vue'
import AvailableComponents from '@js/components/demo/ThemeShowcase.vue'
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import SnackbarDemo from '@js/components/demo/SnackbarDemo.vue'
import ConfirmDialogDemo from '@js/components/demo/ConfirmDialogDemo.vue'
import SessionFlashNotificationsDemo from '@js/components/demo/SessionFlashNotificationsDemo.vue'
import type { AppPageProps } from '@js/contracts/inertia'

const page = usePage<AppPageProps>()
const message = ref<string>('Welcome to the LIVV Stack!')
const user = computed(() => page.props.auth.user)

const demoApps = [
  {
    title: 'AI Chat',
    description: 'Full-featured AI chat with multi-provider support, file attachments, and persistent memory.',
    icon: 'mdi-robot',
    route: '/demo/ai-chat',
    color: 'primary',
    features: [
        'Multiple AI providers (OpenAI, Anthropic, etc.)',
        'File attachments',
        'AI memory system',
        'Session management',
        'Can interact with the Kanban board'
    ],
  },
  {
    title: 'Kanban Board',
    description: 'Drag-and-drop Kanban board for project and task management.',
    icon: 'mdi-view-column',
    route: '/demo/kanban',
    color: 'secondary',
    features: ['Drag & drop tasks', 'Multiple boards', 'Column management', 'Real-time updates'],
  },
]
</script>

<template>
  <AppLayout>
    <v-container>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          class="d-flex flex-column align-center justify-center mt-10"
        >
          <h2>{{ message }}</h2>
          <h4 v-if="user">
            You are logged in as {{ user.email }}
          </h4>
          <h4 v-else>
            You are not logged in
          </h4>
        </v-col>
      </v-row>
      <v-row class="mt-6">
        <v-col
          cols="12"
          class="text-center"
        >
          <h3>Demo Applications</h3>
          <p class="text-medium-emphasis">
            Explore full-featured demo applications built with LIVV Stack
          </p>
        </v-col>
        <v-col
          v-for="app in demoApps"
          :key="app.title"
          cols="12"
          md="6"
        >
          <v-card
            :color="app.color"
            variant="tonal"
            class="h-100"
          >
            <v-card-item>
              <template #prepend>
                <v-avatar
                  :color="app.color"
                  size="48"
                >
                  <v-icon
                    :icon="app.icon"
                    size="24"
                  />
                </v-avatar>
              </template>
              <v-card-title>{{ app.title }}</v-card-title>
              <v-card-subtitle>{{ app.description }}</v-card-subtitle>
            </v-card-item>
            <v-card-text>
              <v-list
                density="compact"
                bg-color="transparent"
              >
                <v-list-item
                  v-for="feature in app.features"
                  :key="feature"
                  :prepend-icon="'mdi-check'"
                  density="compact"
                >
                  <v-list-item-title class="text-body-2">
                    {{ feature }}
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-btn
                :color="app.color"
                variant="flat"
                :href="app.route"
              >
                Open {{ app.title }}
                <v-icon
                  icon="mdi-arrow-right"
                  end
                />
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <v-row class="mt-6">
        <v-col
          cols="12"
          class="text-center"
        >
          <h3>Built In Features</h3>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <SnackbarDemo />
          <SessionFlashNotificationsDemo class="mt-6" />
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <ConfirmDialogDemo />
        </v-col>
      </v-row>
      <AvailableComponents />
    </v-container>
  </AppLayout>
</template>
