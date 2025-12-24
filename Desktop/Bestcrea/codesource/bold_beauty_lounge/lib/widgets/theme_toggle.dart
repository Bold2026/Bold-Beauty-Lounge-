import 'package:flutter/material.dart';
import '../services/theme_service.dart';

class ThemeToggle extends StatefulWidget {
  final bool showLabel;
  final double size;
  final Color? activeColor;
  final Color? inactiveColor;

  const ThemeToggle({
    super.key,
    this.showLabel = true,
    this.size = 24.0,
    this.activeColor,
    this.inactiveColor,
  });

  @override
  State<ThemeToggle> createState() => _ThemeToggleState();
}

class _ThemeToggleState extends State<ThemeToggle>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _animation;
  final ThemeService _themeService = ThemeService();

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _animation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );

    // Initialize animation based on current theme
    if (_themeService.isDarkMode) {
      _animationController.value = 1.0;
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _toggleTheme() async {
    await _themeService.toggleTheme();

    if (_themeService.isDarkMode) {
      _animationController.forward();
    } else {
      _animationController.reverse();
    }

    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final isDark = _themeService.isDarkMode;

    return GestureDetector(
      onTap: _toggleTheme,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isDark
              ? Colors.white.withOpacity(0.1)
              : Colors.black.withOpacity(0.1),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isDark
                ? Colors.white.withOpacity(0.3)
                : Colors.black.withOpacity(0.3),
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Sun icon
            AnimatedBuilder(
              animation: _animation,
              builder: (context, child) {
                return Opacity(
                  opacity: 1.0 - _animation.value,
                  child: Icon(
                    Icons.light_mode,
                    size: widget.size,
                    color:
                        widget.activeColor ??
                        (isDark ? Colors.white70 : const Color(0xFFE9D7C2)),
                  ),
                );
              },
            ),

            const SizedBox(width: 8),

            // Toggle switch
            AnimatedBuilder(
              animation: _animation,
              builder: (context, child) {
                return Container(
                  width: 40,
                  height: 20,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                    color: isDark
                        ? Colors.white.withOpacity(0.2)
                        : Colors.black.withOpacity(0.2),
                  ),
                  child: Stack(
                    children: [
                      // Background
                      Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(10),
                          color: isDark
                              ? Colors.white.withOpacity(0.1)
                              : Colors.black.withOpacity(0.1),
                        ),
                      ),
                      // Sliding circle
                      AnimatedPositioned(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut,
                        left: _animation.value * 20,
                        top: 2,
                        child: Container(
                          width: 16,
                          height: 16,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color:
                                widget.activeColor ?? const Color(0xFFE9D7C2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.2),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),

            const SizedBox(width: 8),

            // Moon icon
            AnimatedBuilder(
              animation: _animation,
              builder: (context, child) {
                return Opacity(
                  opacity: _animation.value,
                  child: Icon(
                    Icons.dark_mode,
                    size: widget.size,
                    color:
                        widget.activeColor ??
                        (isDark ? const Color(0xFFE9D7C2) : Colors.black87),
                  ),
                );
              },
            ),

            if (widget.showLabel) ...[
              const SizedBox(width: 8),
              Text(
                isDark ? 'Sombre' : 'Clair',
                style: TextStyle(
                  color: isDark ? Colors.white70 : Colors.black87,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// Simple theme toggle button
class SimpleThemeToggle extends StatelessWidget {
  final double size;
  final Color? color;

  const SimpleThemeToggle({super.key, this.size = 24.0, this.color});

  @override
  Widget build(BuildContext context) {
    final themeService = ThemeService();
    final isDark = themeService.isDarkMode;

    return IconButton(
      onPressed: () async {
        await themeService.toggleTheme();
        // Force rebuild of the app
        if (context.mounted) {
          (context as Element).markNeedsBuild();
        }
      },
      icon: Icon(
        isDark ? Icons.light_mode : Icons.dark_mode,
        size: size,
        color: color,
      ),
      tooltip: isDark ? 'Mode clair' : 'Mode sombre',
    );
  }
}
