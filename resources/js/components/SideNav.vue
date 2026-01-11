<script setup lang="ts">
import { computed, ref } from "vue";
import { useTheme, useDisplay } from "vuetify";
import { usePage, router } from "@inertiajs/vue3";
import type { AppPageProps } from "@js/contracts/inertia";
import { openVoiceAssistant } from "@js/common/voiceAssistant";

interface NavItem {
    title: string;
    icon: string;
    to?: string;
    action?: () => void;
    requiresAuth?: boolean;
    guestOnly?: boolean;
    activatorId?: string;
}

const theme = useTheme();
const { mdAndUp } = useDisplay();
const page = usePage<AppPageProps>();

const user = computed(() => page.props.auth.user);
const appName = computed(() => page.props.appName);
const isDarkTheme = computed<boolean>(() => theme.global.name.value === "LivvDarkTheme");

const drawer = ref(true);

const navItems: NavItem[] = [
    { title: "Home", icon: "mdi-home", to: "/" },
    { title: "Kanban", icon: "mdi-view-column", to: "/demo/kanban", requiresAuth: true },
    { title: "AI Chat", icon: "mdi-chat", to: "/demo/ai-chat", requiresAuth: true },
    { title: "Realtime Talk", icon: "mdi-microphone", action: () => openVoiceAssistant(), requiresAuth: true },
    { title: "Login", icon: "mdi-login", activatorId: "loginButton", guestOnly: true },
    { title: "Register", icon: "mdi-account-plus", to: "/register", guestOnly: true },
    { title: "Logout", icon: "mdi-logout", to: "/logout", requiresAuth: true },
];

const filteredNavItems = computed(() =>
    navItems.filter((item) => {
        if (item.requiresAuth && !user.value) return false;
        if (item.guestOnly && user.value) return false;
        return true;
    }),
);

function toggleTheme(): void {
    const currentTheme = theme.global.name.value;
    theme.global.name.value = currentTheme === "LivvDarkTheme" ? "LivvLightTheme" : "LivvDarkTheme";
}

function handleNavClick(item: NavItem): void {
    if (item.action) {
        item.action();
    } else if (item.to) {
        router.visit(item.to);
    }
    // Close drawer on mobile after navigation
    if (!mdAndUp.value) {
        drawer.value = false;
    }
}
</script>

<template>
    <!-- Mobile App Bar (visible only on small screens) -->
    <v-app-bar
        v-if="!mdAndUp"
        color="primary"
    >
        <v-app-bar-nav-icon @click="drawer = !drawer" />
        <v-app-bar-title>{{ appName }}</v-app-bar-title>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer
        v-model="drawer"
        :rail="mdAndUp"
        :temporary="!mdAndUp"
        :permanent="mdAndUp"
        color="primary"
    >
        <v-list nav>
            <template
                v-for="item in filteredNavItems"
                :key="item.title"
            >
                <v-tooltip
                    location="end"
                    :disabled="!mdAndUp"
                >
                    <template #activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            :id="item.activatorId"
                            @click="handleNavClick(item)"
                        >
                            <template #prepend>
                                <v-icon>{{ item.icon }}</v-icon>
                            </template>
                            <v-list-item-title v-if="!mdAndUp">
                                {{ item.title }}
                            </v-list-item-title>
                        </v-list-item>
                    </template>
                    {{ item.title }}
                </v-tooltip>
            </template>
        </v-list>

        <!-- Theme toggle at bottom -->
        <template #append>
            <v-list nav>
                <v-tooltip
                    location="end"
                    :disabled="!mdAndUp"
                >
                    <template #activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            @click="toggleTheme"
                        >
                            <template #prepend>
                                <v-icon>{{ isDarkTheme ? "mdi-weather-sunny" : "mdi-weather-night" }}</v-icon>
                            </template>
                            <v-list-item-title v-if="!mdAndUp">
                                {{ isDarkTheme ? "Light Mode" : "Dark Mode" }}
                            </v-list-item-title>
                        </v-list-item>
                    </template>
                    {{ isDarkTheme ? "Switch to Light Mode" : "Switch to Dark Mode" }}
                </v-tooltip>
            </v-list>
        </template>
    </v-navigation-drawer>
</template>

<style scoped lang="css">
</style>
