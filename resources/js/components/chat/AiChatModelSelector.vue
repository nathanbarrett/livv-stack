<script setup lang="ts">
import { computed } from 'vue'
import type { ProviderModelGroup, ProviderModelOption } from '@js/types/chat'

interface Props {
  modelValue: string
  models: ProviderModelGroup[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

interface FlattenedModel extends ProviderModelOption {
  provider: string
  providerLabel: string
  disabled: boolean
}

const flattenedModels = computed<FlattenedModel[]>(() => {
  const result: FlattenedModel[] = []

  for (const group of props.models) {
    for (const model of group.models) {
      result.push({
        ...model,
        provider: group.provider,
        providerLabel: group.providerLabel,
        disabled: !group.enabled,
      })
    }
  }

  return result
})

const selectedModel = computed({
  get: () => props.modelValue,
  set: (value: string) => emit('update:modelValue', value),
})

function customFilter(_value: string, query: string, item?: { raw: FlattenedModel }): boolean {
  if (!item) return false
  const searchText = query.toLowerCase()
  const model = item.raw

  return (
    model.label.toLowerCase().includes(searchText) ||
    model.value.toLowerCase().includes(searchText) ||
    model.providerLabel.toLowerCase().includes(searchText)
  )
}
</script>

<template>
  <v-autocomplete
    v-model="selectedModel"
    :items="flattenedModels"
    item-title="label"
    item-value="value"
    label="Model"
    density="comfortable"
    variant="outlined"
    hide-details
    :custom-filter="customFilter"
  >
    <template #item="{ props: itemProps, item }">
      <v-list-item
        v-bind="itemProps"
        :disabled="item.raw.disabled"
        :subtitle="item.raw.providerLabel"
      >
        <template #prepend>
          <v-avatar
            size="32"
            color="primary"
            variant="tonal"
          >
            <span class="text-caption">{{ item.raw.providerLabel.substring(0, 2).toUpperCase() }}</span>
          </v-avatar>
        </template>
      </v-list-item>
    </template>

    <template #selection="{ item }">
      <div
        v-if="item.raw"
        class="d-flex align-center ga-2 selection-content"
      >
        <v-avatar
          size="24"
          color="primary"
          variant="tonal"
          class="flex-shrink-0"
        >
          <span class="text-caption">{{ item.raw.providerLabel.substring(0, 2).toUpperCase() }}</span>
        </v-avatar>
        <span class="selection-text">{{ item.raw.label }}</span>
      </div>
    </template>
  </v-autocomplete>
</template>

<style scoped>
.selection-content {
  min-width: 0;
  overflow: hidden;
}

.selection-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
