import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/analytics_service.dart';

class NotificationSettingsWidget extends StatefulWidget {
  const NotificationSettingsWidget({super.key});

  @override
  State<NotificationSettingsWidget> createState() =>
      _NotificationSettingsWidgetState();
}

class _NotificationSettingsWidgetState
    extends State<NotificationSettingsWidget> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  // Notification settings
  bool _pushNotifications = true;
  bool _appointmentReminders = true;
  bool _promotionNotifications = true;
  bool _loyaltyNotifications = true;
  bool _reviewNotifications = true;
  bool _marketingNotifications = false;
  bool _systemUpdates = true;
  bool _silentMode = false;

  // Reminder timing
  int _reminderHours = 24; // 24 hours before appointment
  List<int> _reminderOptions = [1, 2, 6, 12, 24, 48]; // hours

  @override
  void initState() {
    super.initState();
    _loadNotificationSettings();
  }

  Future<void> _loadNotificationSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _pushNotifications = prefs.getBool('push_notifications') ?? true;
      _appointmentReminders = prefs.getBool('appointment_reminders') ?? true;
      _promotionNotifications =
          prefs.getBool('promotion_notifications') ?? true;
      _loyaltyNotifications = prefs.getBool('loyalty_notifications') ?? true;
      _reviewNotifications = prefs.getBool('review_notifications') ?? true;
      _marketingNotifications =
          prefs.getBool('marketing_notifications') ?? false;
      _systemUpdates = prefs.getBool('system_updates') ?? true;
      _silentMode = prefs.getBool('silent_mode') ?? false;
      _reminderHours = prefs.getInt('reminder_hours') ?? 24;
    });
  }

  Future<void> _saveNotificationSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('push_notifications', _pushNotifications);
    await prefs.setBool('appointment_reminders', _appointmentReminders);
    await prefs.setBool('promotion_notifications', _promotionNotifications);
    await prefs.setBool('loyalty_notifications', _loyaltyNotifications);
    await prefs.setBool('review_notifications', _reviewNotifications);
    await prefs.setBool('marketing_notifications', _marketingNotifications);
    await prefs.setBool('system_updates', _systemUpdates);
    await prefs.setBool('silent_mode', _silentMode);
    await prefs.setInt('reminder_hours', _reminderHours);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16.0),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                const Icon(
                  Icons.notifications,
                  color: Color(0xFFE9D7C2),
                  size: 24,
                ),
                const SizedBox(width: 12),
                Text(
                  'Notifications',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Spacer(),
                IconButton(
                  onPressed: () {
                    // Navigate to full notification settings
                  },
                  icon: const Icon(
                    Icons.arrow_forward_ios,
                    color: Color(0xFFE9D7C2),
                    size: 16,
                  ),
                ),
              ],
            ),
          ),

          // Notification Settings
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: [
                // Main toggle
                _buildNotificationRow(
                  icon: Icons.notifications_active,
                  title: 'Notifications Push',
                  subtitle: _pushNotifications ? 'Activées' : 'Désactivées',
                  value: _pushNotifications,
                  onChanged: (value) {
                    setState(() {
                      _pushNotifications = value;
                    });
                    _saveNotificationSettings();
                    _analytics.logEvent('notification_toggle', {
                      'type': 'push_notifications',
                      'enabled': value,
                    });
                  },
                ),

                if (_pushNotifications) ...[
                  const Divider(color: Colors.grey),

                  // Appointment reminders
                  _buildNotificationRow(
                    icon: Icons.schedule,
                    title: 'Rappels de RDV',
                    subtitle: 'Rappel ${_reminderHours}h avant',
                    value: _appointmentReminders,
                    onChanged: (value) {
                      setState(() {
                        _appointmentReminders = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // Promotion notifications
                  _buildNotificationRow(
                    icon: Icons.local_offer,
                    title: 'Promotions',
                    subtitle: 'Offres spéciales et réductions',
                    value: _promotionNotifications,
                    onChanged: (value) {
                      setState(() {
                        _promotionNotifications = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // Loyalty notifications
                  _buildNotificationRow(
                    icon: Icons.stars,
                    title: 'Programme de fidélité',
                    subtitle: 'Points et récompenses',
                    value: _loyaltyNotifications,
                    onChanged: (value) {
                      setState(() {
                        _loyaltyNotifications = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // Review notifications
                  _buildNotificationRow(
                    icon: Icons.rate_review,
                    title: 'Demandes d\'avis',
                    subtitle: 'Évaluer vos services',
                    value: _reviewNotifications,
                    onChanged: (value) {
                      setState(() {
                        _reviewNotifications = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // Marketing notifications
                  _buildNotificationRow(
                    icon: Icons.campaign,
                    title: 'Marketing',
                    subtitle: 'Nouvelles et actualités',
                    value: _marketingNotifications,
                    onChanged: (value) {
                      setState(() {
                        _marketingNotifications = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // System updates
                  _buildNotificationRow(
                    icon: Icons.system_update,
                    title: 'Mises à jour système',
                    subtitle: 'Nouvelles fonctionnalités',
                    value: _systemUpdates,
                    onChanged: (value) {
                      setState(() {
                        _systemUpdates = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),

                  const Divider(color: Colors.grey),

                  // Silent mode
                  _buildNotificationRow(
                    icon: Icons.volume_off,
                    title: 'Mode silencieux',
                    subtitle: 'Désactiver les sons',
                    value: _silentMode,
                    onChanged: (value) {
                      setState(() {
                        _silentMode = value;
                      });
                      _saveNotificationSettings();
                    },
                  ),
                ],

                // Reminder timing (only if appointment reminders are enabled)
                if (_pushNotifications && _appointmentReminders) ...[
                  const SizedBox(height: 16),
                  _buildReminderTiming(),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationRow({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return InkWell(
      onTap: () => onChanged(!value),
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12.0),
        child: Row(
          children: [
            Icon(icon, color: const Color(0xFFE9D7C2), size: 24),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(color: Colors.grey[400], fontSize: 14),
                  ),
                ],
              ),
            ),
            Switch(
              value: value,
              onChanged: onChanged,
              activeColor: const Color(0xFFE9D7C2),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReminderTiming() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.access_time, color: Color(0xFFE9D7C2), size: 20),
              const SizedBox(width: 8),
              Text(
                'Rappel ${_reminderHours}h avant le RDV',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _reminderOptions.map((hours) {
              final isSelected = _reminderHours == hours;
              return GestureDetector(
                onTap: () {
                  setState(() {
                    _reminderHours = hours;
                  });
                  _saveNotificationSettings();
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? const Color(0xFFE9D7C2)
                        : Colors.grey[700],
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '${hours}h',
                    style: TextStyle(
                      color: isSelected ? Colors.black : Colors.white,
                      fontWeight: isSelected
                          ? FontWeight.bold
                          : FontWeight.normal,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}









