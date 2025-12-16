<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    chat: {
        type: Object,
        required: true,
    },
});

onMounted(() => {
    const script = document.createElement('script');
    script.src = '/widget.js';
    script.setAttribute('data-chat-id', props.chat.public_id);
    script.id = 'ai-chat-widget-script';
    document.body.appendChild(script);
});

onUnmounted(() => {
    const script = document.getElementById('ai-chat-widget-script');
    if (script) {
        script.remove();
    }
    const container = document.getElementById('ai-chat-widget-container');
    if (container) {
        container.remove();
    }
});
</script>

<template>

    <Head :title="`Демо: ${chat.name}`" />

    <div class="demo-page">
        <div class="container">
            <h1>🤖 {{ chat.name }}</h1>
            <p class="subtitle"> Это демонстрационная страница виджета чат-бота. Нажмите на&nbsp;кнопку чата в&nbsp;правом нижнем углу! </p>
            <div class="features">
                <div class="feature">
                    <h3>💬 Живой чат</h3>
                    <p>Общение с&nbsp;AI в&nbsp;реальном времени</p>
                </div>
                <div class="feature">
                    <h3>🎨 Красивый дизайн</h3>
                    <p>Современный и&nbsp;адаптивный интерфейс</p>
                </div>
                <div class="feature">
                    <h3>⚡ Быстрая интеграция</h3>
                    <p>Одна строка кода для подключения</p>
                </div>
                <div class="feature">
                    <h3>🔒 Изоляция стилей</h3>
                    <p>Shadow DOM защищает от&nbsp;конфликтов</p>
                </div>
                <div class="feature">
                    <h3>🌐 Мультиязычность</h3>
                    <p>Поддержка разных языков общения</p>
                </div>
                <div class="feature">
                    <h3>📊 Аналитика</h3>
                    <p>Статистика и&nbsp;история диалогов</p>
                </div>
            </div>
            <div class="info-block"> <strong>ℹ️ Информация</strong>
                <p> Этот виджет работает на&nbsp;базе искусственного интеллекта и&nbsp;может отвечать на&nbsp;вопросы посетителей вашего сайта 24/7. </p>
            </div>
        </div>

        <footer>
            <Link :href="route('chats.show', chat.id)" class="back-link">
                ← Вернуться к настройкам бота
            </Link>
        </footer>
    </div>
</template>

<style scoped>
.demo-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.container {
    max-width: 800px;
    width: 100%;
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

h1 {
    color: #1a1a1a;
    font-size: 2.5rem;
    margin-bottom: 16px;
}

.subtitle {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 32px;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.feature {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
}

.feature h3 {
    color: #667eea;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.feature p {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.info-block {
    background: #e8f4fd;
    color: #1976d2;
    padding: 16px;
    border-radius: 8px;
    font-size: 0.9rem;
}

.info-block strong {
    display: block;
    margin-bottom: 8px;
}

.info-block p {
    margin: 0;
    line-height: 1.5;
}

footer {
    margin-top: 20px;
    text-align: center;
}

.back-link {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    text-decoration: none;
    transition: color 0.2s;
}

.back-link:hover {
    color: white;
    text-decoration: underline;
}
</style>
