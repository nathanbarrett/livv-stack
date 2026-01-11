<script setup lang="ts">
import SideNav from "@js/components/SideNav.vue";
import BaseLayout from "@js/Pages/BaseLayout.vue";
</script>

<template>
    <BaseLayout>
        <SideNav />
        <v-main>
            <slot />
        </v-main>
    </BaseLayout>
</template>

<style lang="css" scoped>
</style>
