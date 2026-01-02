import 'package:flutter/material.dart';

class WorkingHomeScreen extends StatelessWidget {
  const WorkingHomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Column(
        children: [
          // Header
          _buildHeader(),

          // Content that ACTUALLY SHOWS
          Expanded(child: SingleChildScrollView(child: _buildContent())),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      color: Colors.black,
      padding: const EdgeInsets.all(20),
      width: double.infinity,
      child: Row(
        children: [
          const Icon(Icons.menu, color: Colors.white),
          const SizedBox(width: 12),
          const Text(
            'Bonjour !',
            style: TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const Spacer(),
          const Icon(Icons.location_on, color: Colors.white, size: 20),
          const SizedBox(width: 4),
          const Text(
            'Casablanca',
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Nos Services',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),

          // Service 1
          _buildServiceCard('Coiffure', Colors.blue, Icons.content_cut),
          const SizedBox(height: 12),

          // Service 2
          _buildServiceCard('Onglerie', Colors.pink, Icons.brush),
          const SizedBox(height: 12),

          // Service 3
          _buildServiceCard('Hammam', Colors.orange, Icons.spa),
          const SizedBox(height: 12),

          // Service 4
          _buildServiceCard('Massages', Colors.green, Icons.handshake),
          const SizedBox(height: 12),

          // Service 5
          _buildServiceCard('Head Spa', Colors.purple, Icons.water_drop),
          const SizedBox(height: 12),

          // Service 6
          _buildServiceCard('Soins', Colors.teal, Icons.face),

          const SizedBox(height: 32),

          // Banner
          Container(
            height: 150,
            decoration: BoxDecoration(
              color: const Color(0xFFE9D7C2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Center(
              child: Text(
                'Bold Beauty Lounge',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
          ),

          const SizedBox(height: 32),

          // Quick actions
          Row(
            children: [
              Expanded(
                child: ElevatedButton(
                  onPressed: () {},
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE9D7C2),
                    padding: const EdgeInsets.all(16),
                  ),
                  child: const Text(
                    'Prendre RDV',
                    style: TextStyle(color: Colors.black, fontSize: 16),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  padding: const EdgeInsets.all(16),
                ),
                child: const Icon(Icons.chat, color: Colors.white),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildServiceCard(String name, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[300]!),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 28),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Text(
              name,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w500),
            ),
          ),
          const Icon(Icons.arrow_forward_ios, size: 20, color: Colors.grey),
        ],
      ),
    );
  }
}
