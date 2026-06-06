import { createApp } from 'vue';
import AdminApp from './AdminApp.vue';
import { createAdminRouter } from './router';
import { adminConfig } from './types';
import { configureMrtLogFromRestConfig, installMrtErrorHandlers } from '../utils/mrtLog';
import '../styles/mrt-public.css';
import './styles/admin-shell.css';

function bootAdminApp(): void {
  const el = document.getElementById('mrt-admin-app');
  if (!el) {
    return;
  }
  let cfg;
  try {
    cfg = adminConfig();
  } catch {
    el.innerHTML =
      '<div class="notice notice-error"><p>Vue admin: konfiguration saknas.</p></div>';
    return;
  }
  const router = createAdminRouter(cfg.initialRoute);
  configureMrtLogFromRestConfig(cfg, 'admin');
  const app = createApp(AdminApp);
  installMrtErrorHandlers(app, 'admin');
  app.use(router).mount(el);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootAdminApp);
} else {
  bootAdminApp();
}
