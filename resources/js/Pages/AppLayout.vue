<script setup lang="ts">
import TopNav from "@js/components/TopNav.vue";
import BaseLayout from "@js/Pages/BaseLayout.vue";

</script>

<template>
    <BaseLayout>
        <TopNav />
        <div class="app-body">
            <slot />
        </div>
    </BaseLayout>
</template>

<style lang="css" scoped>
.app-body {
    margin-top: 64px;
}
</style>
