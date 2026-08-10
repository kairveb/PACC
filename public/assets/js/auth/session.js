/* Frontend-only session adapter. Replace its public methods with Laravel/Breeze calls later. */
(() => {
  const storageKey = "himsMainSession";
  const read = () => {
    try {
      const raw = sessionStorage.getItem(storageKey);
      const session = raw ? JSON.parse(raw) : null;
      return session?.authenticated === true ? session : null;
    } catch { return null; }
  };
  const create = ({ name, email, role, id, roles = [] }) => {
    const session = { authenticated: true, user: { id, name, email, role, roles }, createdAt: new Date().toISOString() };
    sessionStorage.setItem(storageKey, JSON.stringify(session));
    return session;
  };
  const clear = () => {
    sessionStorage.removeItem(storageKey);
    sessionStorage.removeItem("himsMainSessionToken");
  };
  window.HimsSession = Object.freeze({ storageKey, read, create, clear, isAuthenticated: () => Boolean(read()), getUser: () => read()?.user || null });
})();
