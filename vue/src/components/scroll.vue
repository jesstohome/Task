<template>
    <div id="wrapper" ref="wrapper" @scroll="onScroll" @touchend="onTouchEnd">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "betterScroll",
    props: {
        handleToScroll: {
            type:Function,
            default:function () {}
        },
        handleToTouch:{
            type: Function,
            default:function () {}
        }
    },
    methods:{
        onScroll(e) {
            const pos = {
                x: -e.target.scrollLeft,
                y: -e.target.scrollTop
            }
            this.handleToScroll(pos)
        },
        onTouchEnd(e) {
            const pos = {
                x: -this.$refs.wrapper.scrollLeft,
                y: -this.$refs.wrapper.scrollTop
            }
            this.handleToTouch(pos)
        },
        scrollToTop(y){
            if (this.$refs.wrapper) {
                this.$refs.wrapper.scrollTop = -y
            }
        }
    }
}
</script>

<style scoped>
    #wrapper{
        height: 100%;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

