/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Application runtime entry point.
 * Loads modular scripts for navigation, countdown, reveal animations, carousel,
 * modal handling, file uploads, and other portal interactions.
 *
 * Status: ✅ Production Ready
 * Version: 1.1 (added file-upload integration)
 */

import './portal/navigation';
import './portal/countdown';
import './portal/reveal';
import './portal/carousel';
import './portal/portal-modal.js';
import './portal/file-upload.js';   // ✅ handles applicant document uploads
import './portal/logout.js';
import './portal/notifications.js';
import './portal/progress.js';
import './portal/unsaved-changes.js';
import './portal/user-menu.js';
