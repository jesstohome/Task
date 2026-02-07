<template>
    <div class="home_box">
        <router-view class="pore" @hideFooter="hideFooter"/>
    </div>
    
</template>
<script>
import {ref} from 'vue'
export default {
    setup(){
        const showFooter = ref(true)
        console.log(showFooter.value)
        const hideFooter = (val) => {
            showFooter.value = !val
        }
        return{hideFooter,showFooter}
    }
}
</script>
<style lang="scss" scoped>
.home_box{
    height: 100vh;
    display: flex;
    flex-direction: column;
    .pore{
        flex: 1;
        overflow: auto;
        display: flex;
        flex-direction: column;
        padding-bottom: 400px;
    }

}
</style>