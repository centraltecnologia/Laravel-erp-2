<template lang="html">
  <div class="card">
    <div  v-sticky="{ zIndex: zIndex, stickyTop: top }" >
      <div class="card-header" v-if="tem_header_slot" >
        <slot name="header"></slot>
      </div>
    </div>
    <div class="card-block">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import VueSticky from 'vue-sticky';

export default {
  directives:{
    'sticky': VueSticky,
  },
  props:{
    top: {
      default: false
    },
    zIndex: {
      default: false
    },
  },
  computed:{
    tem_header_slot () {
      return !!this.$slots['header']
    }
  }
}
</script>
