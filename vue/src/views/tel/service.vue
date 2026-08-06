<template>
    <div class="tel">
        <van-nav-bar :title="$t('msg.kffw')" left-arrow @click-left="$router.go(-1)" />

        <div class="tent">
            <div class="card" v-for="(item, index) in list" :key="index">
                <!-- 头部：头像 + 名称 + 在线状态 -->
                <div class="card-header">
                    <div class="avatar">
                        <span class="avatar-text">{{ item.username.charAt(0) }}</span>
                    </div>
                    <div class="info">
                        <div class="name">{{ item.username }}</div>
                        <div class="status-row">
                            <span class="dot" :class="isOnline(item) ? 'online' : 'offline'"></span>
                            <span class="status-text">{{ isOnline(item) ? $t('msg.zx') : $t('msg.lx') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 工作时间 -->
                <div class="work-time">
                    <van-icon name="clock-o" size="16" />
                    <span>{{ $t('msg.gzsj') }}: {{ item.btime }} - {{ item.etime }}</span>
                </div>

                <!-- 联系按钮 -->
                <van-button block round color="linear-gradient(135deg, #991aff, #7b4fff)" @click="tel(item)">
                    {{ $t('msg.ljzx') }}
                </van-button>
            </div>

            <div class="empty" v-if="list.length === 0">
                <van-empty :description="$t('msg.not_data')" />
            </div>
        </div>
    </div>
</template>
<script>
import { ref, onMounted } from 'vue';
import { getsupport } from '@/api/tel/index';
import { useRouter } from 'vue-router';
import store from '@/store/index';

export default {
    setup() {
        const { push } = useRouter();
        const list = ref([]);

        const isOnline = (item) => {
            if (item.status !== 1) return false;
            const now = new Date();
            const currentMinutes = now.getHours() * 60 + now.getMinutes();

            const [bh, bm] = (item.btime || '00:00').split(':').map(Number);
            const [eh, em] = (item.etime || '00:00').split(':').map(Number);
            const beginMinutes = bh * 60 + bm;
            const endMinutes = eh * 60 + em;

            if (endMinutes >= beginMinutes) {
                return currentMinutes >= beginMinutes && currentMinutes <= endMinutes;
            }
            // 跨天场景（如 22:00 - 02:00）
            return currentMinutes >= beginMinutes || currentMinutes <= endMinutes;
        };

        const tel = (row) => {
            window.location.href = row.url + '&metadata={"name":"' + store.state.userinfo.username + '","comment":"UserID:' + store.state.userinfo.id + '"}';
        };

        onMounted(() => {
            store.dispatch('changefooCheck', 'tel');
            getsupport().then(res => {
                if (res.code === 0) {
                    list.value = res.data || [];
                }
            });
        });

        return { list, tel, isOnline };
    }
};
</script>
<style lang="scss" scoped>
@import '@/styles/theme.scss';

.tel {
    min-height: 100vh;
    background: $bg-primary;

    :deep(.van-nav-bar) {
        background-color: $theme;
        .van-nav-bar__content { height: 80px; }
        .van-nav-bar__left .van-icon { color: #fff; font-size: 30px; }
        .van-nav-bar__title {
            color: #fff;
            font-weight: 600;
            font-size: 32px;
            line-height: 60px;
        }
    }

    .tent {
        padding: 30px 30px 0;

        .card {
            background: $bg-card;
            border-radius: 20px;
            padding: 36px 32px 32px;
            margin-bottom: 24px;
            box-shadow: $shadow;
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 24px;

            .avatar {
                width: 88px;
                height: 88px;
                border-radius: 50%;
                background: linear-gradient(135deg, #991aff, #7b4fff);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin-right: 24px;

                .avatar-text {
                    font-size: 36px;
                    font-weight: 700;
                    color: #fff;
                }
            }

            .info {
                flex: 1;
                min-width: 0;

                .name {
                    font-size: 32px;
                    font-weight: 600;
                    color: $textColor;
                    margin-bottom: 10px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .status-row {
                    display: flex;
                    align-items: center;

                    .dot {
                        width: 14px;
                        height: 14px;
                        border-radius: 50%;
                        margin-right: 8px;
                        flex-shrink: 0;

                        &.online {
                            background: #22c55e;
                            box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
                        }
                        &.offline {
                            background: #9ca3af;
                        }
                    }

                    .status-text {
                        font-size: 24px;
                        color: $textSecondary;
                    }
                }
            }
        }

        .work-time {
            display: flex;
            align-items: center;
            padding: 20px 24px;
            background: $bg-card-hover;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 26px;
            color: $textSecondary;

            :deep(.van-icon) {
                color: $theme;
                margin-right: 10px;
            }
        }

        :deep(.van-button) {
            height: 80px;
            font-size: 30px;
            font-weight: 600;
            border: none;
            letter-spacing: 2px;
        }

        .empty {
            padding-top: 200px;
        }
    }
}
</style>
