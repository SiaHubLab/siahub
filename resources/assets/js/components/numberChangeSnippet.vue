<template>
    <div :class="'text-center '+((value >= 0) ? 'network-change-success':'network-change-danger')">
      <span v-if="value >= 0">
          +{{value}} {{suffix}}
      </span>
      <span v-else>
          {{value}} {{suffix}}
      </span>
    </div>
</template>

<script>
export default {
    props: ['value', 'suffix']
}
</script>
