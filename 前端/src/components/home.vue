<template>
  <div class="basic-layout">
    <div class="nav-side"></div>
    <div class="content-right">
      <div class="nav-top">
        <div class="bread">斷點續傳Demo</div>
      </div>
      <el-main>
        <router-view></router-view>
      </el-main>
    </div>
  </div>
</template>

<script>
export default {
  name: "Home",
};
</script>

<style lang="scss">
.basic-layout {
  position: relative;
  .nav-side {
    position: fixed;
    width: 50px;
    height: 100vh;
    background-color: #001529;
    color: #fff;
    // overflow-y: auto;
    transition: 0.5s;
  }
  .content-right {
    margin-left: 50px;
    .nav-top {
      height: 50px;
      line-height: 50px;
      display: flex;
      border-bottom: 1px solid #ddd;
      justify-content: space-between;
      padding: 0 20px;
    }
    .wrapper {
      background: #eef0f3;
      padding: 20px;
      height: calc(100vh - 100px);
      .main-page {
        background-color: #fff;
        height: 100%;
      }
    }
  }
}
</style>
