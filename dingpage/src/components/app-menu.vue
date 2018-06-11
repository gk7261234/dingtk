<template>
  <div class="app">
    <grid class="dt-grids" :cols="4" :show-lr-borders="false" :show-vertical-dividers="false">
      <grid-item class="dt-grid" label="项目复核" link="/review/list">
        <img class="dt-grid__icon" slot="icon" src="../assets/icon/icon_fk_fill.svg">
      </grid-item>
    </grid>
  </div>
</template>

<script>
  import { Group, Cell, XButton, Grid, GridItem } from 'vux'

  export default {
    components: {
      Group,
      Cell,
      XButton,
      Grid,
      GridItem
    }
  }
</script>

<style>
  body {
    background-color: white;
  }

  .dt-grids {
    background-color: white;
  }

  .dt-grids:before {
    border-top: none !important;
  }

  .dt-grid:after {
    border-bottom: none !important;
  }

  .dt-grid .weui-grid__icon {
    width: 48px;
    height: 48px;
  }

  .dt-grid__icon {
    border-radius: 6px;
  }

  .dt-grid .weui-grid__label {
    font-size: 12px;
    color: #222222;
  }
</style>
